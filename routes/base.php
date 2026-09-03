<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Http\Controllers\Base;
use Pterodactyl\Http\Controllers\Install\NodeInstallController;
use Pterodactyl\Http\Middleware\RequireTwoFactorAuthentication;

Route::get('/install.sh', function () {
    $path = base_path('install.sh');
    if (!file_exists($path)) {
        abort(404);
    }
    return response(file_get_contents($path), 200)
        ->header('Content-Type', 'application/x-sh')
        ->header('Content-Disposition', 'inline; filename="install.sh"');
})->name('install.sh');

Route::group(['prefix' => 'node-install/{identifier}'], function () {
    Route::get('/', [NodeInstallController::class, 'download'])->name('node-install');
    Route::get('/verify', [NodeInstallController::class, 'verifyEnvironment'])->name('node-install.verify');
    Route::get('/wings-config', [NodeInstallController::class, 'getWingsConfig'])->name('node-install.config');
});

Route::get('/', [Base\IndexController::class, 'index'])->name('index')->fallback();
Route::get('/account', [Base\IndexController::class, 'index'])
    ->withoutMiddleware(RequireTwoFactorAuthentication::class)
    ->name('account');

Route::get('/locales/locale.json', Base\LocaleController::class)
    ->withoutMiddleware(['auth', RequireTwoFactorAuthentication::class])
    ->where('namespace', '.*');

Route::get('/{react}', [Base\IndexController::class, 'index'])
    ->where('react', '^(?!(\/)?(api|auth|admin|daemon|node-install)).+');
