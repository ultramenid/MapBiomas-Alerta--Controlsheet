<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UsersController;
use App\Http\Middleware\checkLevel;
use App\Http\Middleware\checkRole;
use App\Http\Middleware\checkSession;
use App\Http\Middleware\hasSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::middleware([checkSession::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/addalert', [AlertController::class, 'addalert']);
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::get('/alerts', [AlertController::class, 'index']);
    Route::get('/editalert/{id}', [AlertController::class, 'editalert']);
    route::get('/rest/fix/{id}', [AlertController::class, 'fix']);
    route::get('/rest/audit/{id}', [AlertController::class, 'audit']);
    route::get('/rest/audit-test/{id}', [AlertController::class, 'auditTest']);
    route::get('/alerts-test', [AlertController::class, 'alertsTest']);

    // Signs the header's "online users" presence subscription. Login here is a plain
    // session (no Auth guard), so Laravel's own /broadcasting/auth always 403s.
    // The channel name is fixed, so this can't be used to sign any other channel.
    Route::post('/online/auth', fn (Request $request) => json_decode(
        Broadcast::connection('reverb')->getPusher()->authorizePresenceChannel('presence-online', (string) $request->input('socket_id'), (string) session('id'), ['name' => session('name')]),
        true
    ));

    Route::middleware([checkLevel::class])->group(function(){
        Route::get('/users', [UsersController::class, 'index']);
        Route::get('/adduser', [UsersController::class, 'adduser']);
        Route::get('/edituser/{id}', [UsersController::class, 'edituser']);
        Route::get('/auditor-alert/{id}', [AlertController::class, 'auditorAlert']);
    });

    Route::middleware([checkRole::class])->group(function(){
        Route::get('/alertanalis/{id}', [AlertController::class, 'alertanalis']);
    });

    Route::group(['prefix' => 'cms/controlsheet-filemanager'], function () {
        \UniSharp\LaravelFilemanager\Lfm::routes();
    });

});


Route::middleware([hasSession::class])->group(function () {
    Route::get('/', [IndexController::class, 'index']);
    // Route::get('/', function () {
    //     return view('migration');
    // });
});


Route::get('logout', function () {
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
});
