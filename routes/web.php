<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;


Route::get('/test', [TestController::class, 'test']);
Route::get('/api/articles', [ArticleController::class, 'index']);
