<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UploadSessionController;

Route::group(['middleware' => 'guest'], function () {
    Route::view('/login', 'login');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});
Route::group(['middleware' => 'auth'], function () {
    Route::view('/', 'index');
    Route::put('/upload', [UploadController::class, 'upload'])->name('upload');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/sessions', [UploadSessionController::class, 'index'])->name('uploadsession.index');
    Route::view('/sessions/new', 'sessions/new');
    Route::post('/sessions/new', [UploadSessionController::class, 'newSessionView']);
    Route::get('/sessions/{id}', [UploadSessionController::class, 'viewSession'])->name('uploadsession.view');
    Route::post('/sessions/{id}/delete', [UploadSessionController::class, 'deleteSession'])->name('uploadsession.delete');
    Route::post('/api/session', [UploadSessionController::class, 'newSessionApi']);
    Route::get('/api/upload/{id}', [UploadController::class, 'getUploadApi']);
});
