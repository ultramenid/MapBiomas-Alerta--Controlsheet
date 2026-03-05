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
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

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
});


Route::get('logout', function () {
    session()->flush();
    return redirect('/');
});
