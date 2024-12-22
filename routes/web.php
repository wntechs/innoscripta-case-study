<?php

use App\Http\Controllers\Api\ArticleController;

use Illuminate\Support\Facades\Route;


Route::get('/api/articles', [ArticleController::class, 'index']);
