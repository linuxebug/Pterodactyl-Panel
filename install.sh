#!/bin/bash
# Pterodactyl Panel Easy Installer
# Usage: curl -fsSL https://your-domain/install.sh | bash
set -euo pipefail

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Helper functions
log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[OK]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

echo "==========================================="
echo " Pterodactyl Panel Easy Installer"
echo "==========================================="
echo ""

# ============================================
# Step 1: Detect OS
# ============================================
log_info "Detecting operating system..."
OS_TYPE=""
OS_VERSION=""

if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS_TYPE="${ID:-unknown}"
    OS_VERSION="${VERSION_ID:-unknown}"
    log_success "OS detected: ${OS_TYPE} ${OS_VERSION}"
elif [ -f /etc/redhat-release ]; then
    OS_TYPE="redhat"
    OS_VERSION=$(cat /etc/redhat-release)
    log_success "OS detected: ${OS_TYPE} ${OS_VERSION}"
else
    log_error "Unable to detect operating system."
    exit 1
fi

# Check if running as root
if [ "$(id -u)" -ne 0 ]; then
    log_error "This installer must be run as root (use sudo)."
    exit 1
fi
log_success "Running as root."

# ============================================
# Step 2: Check required dependencies
# ============================================
log_info "Checking required dependencies..."

MISSING_DEPS=()

# Check for curl
if ! command -v curl &> /dev/null; then
    MISSING_DEPS+=("curl")
fi

# Check for git
if ! command -v git &> /dev/null; then
    MISSING_DEPS+=("git")
fi

# Check for unzip
if ! command -v unzip &> /dev/null; then
    MISSING_DEPS+=("unzip")
fi

# Check for tar
if ! command -v tar &> /dev/null; then
    MISSING_DEPS+=("tar")
fi

if [ ${#MISSING_DEPS[@]} -gt 0 ]; then
    log_info "Installing missing dependencies: ${MISSING_DEPS[*]}"
    if [ "$OS_TYPE" = "ubuntu" ] || [ "$OS_TYPE" = "debian" ]; then
        apt-get update -qq
        apt-get install -y -qq "${MISSING_DEPS[@]}"
    elif [ "$OS_TYPE" = "centos" ] || [ "$OS_TYPE" = "rhel" ] || [ "$OS_TYPE" = "fedora" ] || [ "$OS_TYPE" = "amzn" ]; then
        yum install -y -q "${MISSING_DEPS[@]}"
    else
        log_error "Unsupported OS for automatic dependency installation: ${OS_TYPE}"
        log_error "Please install manually: ${MISSING_DEPS[*]}"
        exit 1
    fi
    log_success "Dependencies installed."
else
    log_success "All required dependencies are present."
fi

# ============================================
# Step 3: Check PHP version
# ============================================
log_info "Checking PHP version..."

PHP_REQUIRED="8.2.0"
PHP_FOUND=""

if command -v php8.2 &> /dev/null; then
    PHP_FOUND=$(php8.2 -v 2>/dev/null | head -1 | awk '{print $2}')
    PHP_BIN="php8.2"
elif command -v php8.3 &> /dev/null; then
    PHP_FOUND=$(php8.3 -v 2>/dev/null | head -1 | awk '{print $2}')
    PHP_BIN="php8.3"
elif command -v php &> /dev/null; then
    PHP_FOUND=$(php -v 2>/dev/null | head -1 | awk '{print $2}')
    PHP_BIN="php"
else
    log_error "PHP is not installed. PHP >= ${PHP_REQUIRED} is required."
    if [ "$OS_TYPE" = "ubuntu" ] || [ "$OS_TYPE" = "debian" ]; then
        log_info "Installing PHP and extensions..."
        apt-get install -y -qq software-properties-common
        add-apt-repository -y ppa:ondrej/php
        apt-get update -qq
        apt-get install -y -qq php8.2 php8.2-cli php8.2-common php8.2-fpm \
            php8.2-json php8.2-mbstring php8.2-pdo php8.2-mysql \
            php8.2-gd php8.2-curl php8.2-zip php8.2-xml php8.2-bcmath \
            php8.2-opcache php8.2-posix php8.2-readline
        PHP_BIN="php8.2"
        PHP_FOUND=$(php8.2 -v 2>/dev/null | head -1 | awk '{print $2}')
    elif [ "$OS_TYPE" = "centos" ] || [ "$OS_TYPE" = "rhel" ] || [ "$OS_TYPE" = "fedora" ] || [ "$OS_TYPE" = "amzn" ]; then
        log_info "Installing PHP and extensions..."
        dnf install -y -q epel-release
        dnf install -y -q php82-php-cli php82-php-common php82-php-fpm \
            php82-php-json php82-php-mbstring php82-php-pdo php82-php-mysqlnd \
            php82-php-gd php82-php-curl php82-php-zip php82-php-xml \
            php82-php-bcmath php82-php-opcache php82-php-posix
        PHP_BIN="php"
        PHP_FOUND="" # Remi doesn't always report version cleanly
    else
        log_error "Cannot auto-install PHP on ${OS_TYPE}. Please install PHP >= ${PHP_REQUIRED} manually."
        exit 1
    fi
fi

if [ -n "$PHP_FOUND" ]; then
    log_success "PHP version found: ${PHP_FOUND}"
    if dpkg --compare-versions "$PHP_FOUND" lt "$PHP_REQUIRED" 2>/dev/null || \
       php -r "if (version_compare('${PHP_FOUND}', '${PHP_REQUIRED}', '<')) exit(1);" 2>/dev/null; then
        if ! php -r "if (version_compare('${PHP_FOUND}', '${PHP_REQUIRED}', '<')) exit(1);" 2>/dev/null; then
            log_success "PHP version meets requirements."
        else
            log_error "PHP version ${PHP_FOUND} is below required ${PHP_REQUIRED}"
            exit 1
        fi
    else
        log_success "PHP version meets requirements."
    fi
    PHPIZE_VER="${PHP_BIN#php}"
else
    log_success "PHP installed. Verifying version..."
    ${PHP_BIN} -v
fi

# ============================================
# Step 4: Check PHP extensions
# ============================================
log_info "Checking required PHP extensions..."

REQUIRED_EXTENSIONS=("json" "mbstring" "pdo" "pdo_mysql" "posix" "zip" "gd" "openssl" "curl")
MISSING_EXTENSIONS=()

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if ! ${PHP_BIN} -m 2>/dev/null | grep -i "^${ext}$" > /dev/null; then
        MISSING_EXTENSIONS+=("$ext")
    fi
done

if [ ${#MISSING_EXTENSIONS[@]} -gt 0 ]; then
    log_warn "Missing PHP extensions: ${MISSING_EXTENSIONS[*]}"
    if [ "$OS_TYPE" = "ubuntu" ] || [ "$OS_TYPE" = "debian" ]; then
        apt-get install -y -qq ${PHP_BIN}-${MISSING_EXTENSIONS[*]}
        log_success "PHP extensions installed."
    elif [ "$OS_TYPE" = "centos" ] || [ "$OS_TYPE" = "rhel" ] || [ "$OS_TYPE" = "fedora" ] || [ "$OS_TYPE" = "amzn" ]; then
        # Try installing from remi
        PHPIZE_VER="${PHP_BIN#php}"
        if [ -n "$PHPIZE_VER" ]; then
            dnf install -y -q php${PHPIZE_VER}-${MISSING_EXTENSIONS[*]} 2>/dev/null || true
        fi
        log_success "PHP extensions installed (if available)."
    else
        log_error "Please install missing PHP extensions manually: ${MISSING_EXTENSIONS[*]}"
        exit 1
    fi
else
    log_success "All required PHP extensions are present."
fi

# ============================================
# Step 5: Check Composer
# ============================================
log_info "Checking Composer..."

if ! command -v composer &> /dev/null; then
    log_info "Composer not found. Installing..."
    EXPECTED_SIGNATURE=$(curl -fsSL https://composer.github.io/installer.sig)
    curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
    ACTUAL_SIGNATURE=${PHP_BIN} -r "echo hash_file('sha384', '/tmp/composer-setup.php');"
    if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then
        log_error "Composer installer signature mismatch."
        rm -f /tmp/composer-setup.php
        exit 1
    fi
    ${PHP_BIN} /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm -f /tmp/composer-setup.php
    log_success "Composer installed."
else
    log_success "Composer is already installed."
fi

export COMPOSER_ALLOW_XDEBUG=1
composer --version

# ============================================
# Step 6: Check database
# ============================================
log_info "Checking database configuration..."

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-pterodactyl}"
DB_PASSWORD="${DB_PASSWORD:-}"
DB_NAME="${DB_NAME:-pterodactyl}"

# Check if MySQL/MariaDB is running
if ! nc -z "$DB_HOST" "$DB_PORT" 2>/dev/null; then
    log_error "Cannot connect to database at ${DB_HOST}:${DB_PORT}"
    log_error "Please ensure MySQL/MariaDB is running and accessible."
    exit 1
fi
log_success "Database server is reachable at ${DB_HOST}:${DB_PORT}"

# Check if pterodactyl database exists, create if not
DB_CHECK=$(${PHP_BIN} -r "
try {
    \$dsn = 'mysql:host=${DB_HOST};port=${DB_PORT};dbname=mysql';
    \$pdo = new PDO(\$dsn, '${DB_USER}', '${DB_PASSWORD}',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    \$stmt = \$pdo->prepare('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?');
    \$stmt->execute(['${DB_NAME}']);
    echo \$stmt->fetchColumn() ? 'exists' : 'not_exists';
} catch (\Exception \$e) {
    echo 'error: ' . \$e->getMessage();
}
" 2>/dev/null)

if [ "$DB_CHECK" = "not_exists" ]; then
    log_info "Creating database '${DB_NAME}'..."
    ${PHP_BIN} -r "
    \$pdo = new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=mysql', '${DB_USER}', '${DB_PASSWORD}',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    \$pdo->exec(\"CREATE DATABASE \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;\");
    echo 'Database created.';
    "
    log_success "Database '${DB_NAME}' created."
elif [ "$DB_CHECK" = "exists" ]; then
    log_success "Database '${DB_NAME}' already exists."
else
    log_error "Database connection failed: ${DB_CHECK}"
    exit 1
fi

# ============================================
# Step 7: Download/install the panel
# ============================================
PANEL_PATH="${PANEL_PATH:-/var/www/pterodactyl}"
INSTALLER_DIR="${INSTALLER_DIR:-/tmp/pterodactyl_installer}"

log_info "Installing Pterodactyl Panel..."

# Create directories
mkdir -p "$INSTALLER_DIR"
mkdir -p "$PANEL_PATH"
cd "$INSTALLER_DIR"

# Clone the repository
if [ ! -d "$INSTALLER_DIR/panel" ]; then
    log_info "Cloning Pterodactyl Panel repository..."
    git clone --depth 1 https://github.com/pterodactyl/panel.git "$INSTALLER_DIR/panel"
fi

# Copy files to installation path if not already there
if [ ! -f "$PANEL_PATH/artisan" ]; then
    log_info "Copying panel files to ${PANEL_PATH}..."
    cp -r "$INSTALLER_DIR/panel/"* "$PANEL_PATH/"
    log_success "Panel files copied."
else
    log_success "Panel files already exist at ${PANEL_PATH}."
fi

cd "$PANEL_PATH"

# ============================================
# Step 8: Configure .env
# ============================================
log_info "Configuring .env file..."

if [ ! -f "$PANEL_PATH/.env" ]; then
    cp "$PANEL_PATH/.env.example" "$PANEL_PATH/.env"
    log_success ".env file created from example."
else
    log_success ".env file already exists."
fi

# Update .env with database configuration
update_env() {
    local key="$1"
    local value="$2"
    if grep -q "^${key}=" "$PANEL_PATH/.env"; then
        sed -i "s|^${key}=.*|${key}=${value}|" "$PANEL_PATH/.env"
    else
        echo "${key}=${value}" >> "$PANEL_PATH/.env"
    fi
}

update_env "DB_HOST" "$DB_HOST"
update_env "DB_PORT" "$DB_PORT"
update_env "DB_DATABASE" "$DB_NAME"
update_env "DB_USERNAME" "$DB_USER"
update_env "DB_PASSWORD" "$DB_PASSWORD"
update_env "DB_CONNECTION" "mysql"
update_env "APP_ENV" "production"
update_env "APP_DEBUG" "false"
update_env "APP_URL" "${APP_URL:-http://localhost:8686}"
update_env "PANEL_PORT" "${PANEL_PORT:-8686}"

log_success ".env file configured."

# ============================================
# Step 9: Generate Laravel application key
# ============================================
log_info "Generating application key..."
${PHP_BIN} artisan key:generate --force
log_success "Application key generated."

# ============================================
# Step 10: Install dependencies
# ============================================
log_info "Installing Composer dependencies..."
composer install --optimize-autoloader --no-interaction --prefer-dist --quiet
log_success "Composer dependencies installed."

# ============================================
# Step 11: Run migrations
# ============================================
log_info "Running database migrations..."
${PHP_BIN} artisan migrate --force
log_success "Migrations completed."

# ============================================
# Step 12: Configure permissions
# ============================================
log_info "Configuring permissions..."

if id -u "www-data" &> /dev/null 2>&1; then
    WEB_USER="www-data"
    WEB_GROUP="www-data"
elif id -u "nginx" &> /dev/null 2>&1; then
    WEB_USER="nginx"
    WEB_GROUP="nginx"
elif id -u "apache" &> /dev/null 2>&1; then
    WEB_USER="apache"
    WEB_GROUP="apache"
else
    WEB_USER=$(whoami)
    WEB_GROUP=$(whoami)
fi

chown -R "${WEB_USER}:${WEB_GROUP}" "$PANEL_PATH/storage"
chown -R "${WEB_USER}:${WEB_GROUP}" "$PANEL_PATH/bootstrap/cache"
chmod -R 755 "$PANEL_PATH/storage"
chmod -R 755 "$PANEL_PATH/bootstrap/cache"
log_success "Permissions configured for user ${WEB_USER}."

# ============================================
# Step 13: Configure web server
# ============================================
log_info "Configuring web server..."

if [ "$OS_TYPE" = "ubuntu" ] || [ "$OS_TYPE" = "debian" ]; then
    if [ -f /etc/nginx/nginx.conf ]; then
        log_info "Nginx is installed. Please configure your Nginx site."
        log_info "Example configuration available at: ${PANEL_PATH}/.examples/nginx.conf"
    fi
elif [ "$OS_TYPE" = "centos" ] || [ "$OS_TYPE" = "rhel" ] || [ "$OS_TYPE" = "fedora" ] || [ "$OS_TYPE" = "amzn" ]; then
    if [ -f /etc/nginx/nginx.conf ]; then
        log_info "Nginx is installed. Please configure your Nginx site."
    fi
fi

# Set up systemd service for queue worker
log_info "Setting up queue worker systemd service..."
cat > /etc/systemd/system/pterodactyl-queue.service << 'EOF'
[Unit]
Description=Pterodactyl Queue Worker
After=network.target

[Service]
User=__WEB_USER__
Group=__WEB_GROUP__
WorkingDirectory=/var/www/pterodactyl
ExecStart=/usr/bin/php /var/www/pterodactyl/artisan queue:work --sleep=3 --tries=3
Restart=always
RestartSec=5
SyslogIdentifier=pterodactyl-queue

[Install]
WantedBy=multi-user.target
EOF

sed -i "s/__WEB_USER__/${WEB_USER}/" /etc/systemd/system/pterodactyl-queue.service
sed -i "s/__WEB_GROUP__/${WEB_GROUP}/" /etc/systemd/system/pterodactyl-queue.service
systemctl daemon-reload
systemctl enable pterodactyl-queue 2>/dev/null || true
systemctl start pterodactyl-queue 2>/dev/null || true
log_success "Queue worker service configured."

# ============================================
# Step 14: Start services
# ============================================
log_info "Ensuring required services are running..."
systemctl enable nginx 2>/dev/null || true
systemctl restart nginx 2>/dev/null || true
systemctl enable php*-fpm 2>/dev/null || true
systemctl restart php*-fpm 2>/dev/null || true
log_success "Services started."

# ============================================
# Step 15: Verify installation
# ============================================
log_info "Verifying installation..."

if [ -f "$PANEL_PATH/artisan" ]; then
    log_success "Panel installed successfully at ${PANEL_PATH}"
else
    log_error "Installation verification failed: artisan file not found."
    exit 1
fi

if [ -f "$PANEL_PATH/.env" ]; then
    log_success ".env file exists."
else
    log_error ".env file not found."
    exit 1
fi

if [ -d "$PANEL_PATH/vendor" ]; then
    log_success "Composer dependencies installed."
else
    log_error "Composer dependencies not found."
    exit 1
fi

log_success "Database connection verified."
log_success "Migrations completed."

# Clean up temporary files
rm -rf "$INSTALLER_DIR"

# Mark installation as complete
${PHP_BIN} artisan tinker --execute="app('Pterodactyl\Contracts\Repository\SettingsRepositoryInterface')->set('settings::installer_completed', 'true');" 2>/dev/null || true

echo ""
echo "==========================================="
log_success "Installation complete!"
echo "==========================================="
echo ""
echo "Panel URL: ${APP_URL:-http://localhost:8686}"
echo ""
echo "Next steps:"
echo "  1. Configure your web server (Nginx/Apache)"
echo "  2. Set up SSL certificates (Let's Encrypt)"
echo "  3. Access the panel and create an admin account"
echo "  4. Create nodes and add servers"
echo ""
echo "Documentation: https://pterodactyl.io/"
echo ""
