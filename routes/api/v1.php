<?php

use App\Http\Controllers\Api\V1\ApplicationApiController;
use App\Http\Controllers\Api\V1\BuildApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/applications', [ApplicationApiController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [ApplicationApiController::class, 'get'])->name('applications.get');
    Route::post('/applications', [ApplicationApiController::class, 'create'])->name('applications.create');
    Route::get('/applications/{application:slug}/events', [ApplicationApiController::class, 'events'])->name('applications.events');

    Route::post('/applications/{application}/builds', [BuildApiController::class, 'create'])->name('builds.create');
    Route::get('/applications/{application}/builds', [BuildApiController::class, 'index'])->name('applications.builds');

    Route::post('/builds/{build}', [BuildApiController::class, 'update'])->name('builds.update');
    Route::delete('/builds/{build}', [BuildApiController::class, 'delete'])->name('builds.delete');
});

Route::get('/applications/{application:slug}/updates/{platform}/{version}', [ApplicationApiController::class, 'updates'])->name('updates.get');
Route::get('/applications/{application:slug}/{platform}/latest', [BuildApiController::class, 'latest'])->name('applications.builds.latest');
