<?php

use App\Http\Controllers\EmbeddingCallbackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/embedding-callback', [EmbeddingCallbackController::class, 'handle']);