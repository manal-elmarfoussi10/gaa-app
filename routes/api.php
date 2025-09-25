<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhooks\YousignWebhookController;

Route::post('/webhooks/yousign', [YousignWebhookController::class, 'handle'])
    ->name('webhooks.yousign');