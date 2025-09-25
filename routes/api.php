<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhooks\YousignWebhookController;

// CSRF-free API route (note the /api prefix will be added automatically)
Route::post('/webhooks/yousign', [YousignWebhookController::class, 'handle'])
    ->name('webhooks.yousign');