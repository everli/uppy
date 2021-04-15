<?php


use App\Http\Controllers\Api\V2\ApplicationApiController;
use Illuminate\Support\Facades\Route;

Route::post('/applications/{application:slug}/updates/{platform}/', [ApplicationApiController::class, 'updates'])->name('updates.get');
