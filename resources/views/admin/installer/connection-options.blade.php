@extends('layouts.admin')

@section('title')
    Connection Options
@endsection

@section('content-header')
    <h1>Connection Options<small>Configure how to connect to your panel.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.installer.complete') }}">Installation Complete</a></li>
        <li class="active">Connection Options</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> The panel application and a Pterodactyl Wings node are separate components.
            This page shows how to connect to your panel.
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Option 1 — Connect Your Domain</h3>
            </div>
            <div class="box-body">
                <p>To use a custom domain with your panel, you need:</p>
                <ol>
                    <li><strong>Set APP_URL in .env</strong> — Set <code>APP_URL=https://your-domain.com</code> in your <code>.env</code> file.</li>
                    <li><strong>Point DNS to your server</strong> — Create an A record pointing your domain to your server's IP address (or CNAME for proxy-based setups).</li>
                    <li><strong>Install SSL certificate</strong> — Use Let's Encrypt: <code>sudo certbot certonly --standalone -d your-domain.com</code></li>
                    <li><strong>Configure reverse proxy</strong> — <a href="https://pterodactyl.io/panel/1.0/webserver.html" target="_blank">Follow the Pterodactyl web server configuration guide</a> for Nginx or Apache.</li>
                    <li><strong>Cloudflare Tunnel</strong> — If using Cloudflare Tunnel, you must configure it yourself. The panel does not automatically configure tunnels.</li>
                </ol>

                <div class="form-group">
                    <label for="pDomain" class="form-label">Your Domain</label>
                    <input type="url" id="pDomain" class="form-control" placeholder="https://your-domain.com" />
                    <p class="text-muted small">Set this in your <code>APP_URL</code> environment variable.</p>
                </div>
            </div>
        </div>

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Option 2 — Use Locally</h3>
            </div>
            <div class="box-body">
                <p>Access the panel on your local machine:</p>
                <div class="alert alert-success">
                    <strong>Local Address:</strong>
                    <code>http://127.0.0.1:{{ $environment['panel_port'] }}</code>
                </div>
                <p>Panel Port: <code>{{ $environment['panel_port'] }}</code> (configurable via <code>PANEL_PORT</code> environment variable)</p>

                @if(!$environment['has_docker'])
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i> Docker is not available in this environment.
                    To run Wings (node daemon), you need a Linux server with Docker installed.
                </div>
                @endif
            </div>
        </div>

        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Option 3 — GitHub / CodeSandbox</h3>
            </div>
            <div class="box-body">
                <p>Detected environment: <strong>{{ $environment['environment'] }}</strong></p>

                @if($environment['public_url'])
                <div class="alert alert-success">
                    <strong>Public URL:</strong>
                    <code>{{ $environment['public_url'] }}</code>
                </div>
                @endif

                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i> <strong>Important:</strong> The panel application and a Pterodactyl Wings node are separate components.
                    GitHub Codespaces / CodeSandbox can run the panel for testing, but <strong>cannot run Wings or Docker containers</strong>.
                    To host actual game servers, you need a separate Linux server with Docker.
                </div>

                @if($environment['limitations'])
                <ul class="text-danger">
                    @foreach($environment['limitations'] as $limitation)
                    <li>{{ $limitation }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Architecture Overview</h3>
            </div>
            <div class="box-body">
                <ul class="list-group">
                    <li class="list-group-item">
                        <strong>Panel</strong>
                        <small class="text-muted">Runs on this server. Manages users, servers, nodes.</small>
                    </li>
                    <li class="list-group-item">
                        <strong>Wings Daemon</strong>
                        <small class="text-muted">Runs on each node machine. Manages Docker containers.</small>
                    </li>
                    <li class="list-group-item">
                        <strong>Database</strong>
                        <small class="text-muted">Shared between Panel and Wings nodes.</small>
                    </li>
                </ul>
                <p class="text-muted small">The panel does NOT replace Wings. A node becomes online only when the real Wings daemon connects.</p>
            </div>
        </div>
    </div>
</div>
@endsection
