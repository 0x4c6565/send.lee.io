<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

Route::withoutMiddleware('web')->group(function () {
    Route::put('/{any}', [UploadController::class, 'upload']);
    Route::get('/download/{id}', [UploadController::class, 'download']);
    Route::get('/d/{id}', [UploadController::class, 'downloadForm']);
});
