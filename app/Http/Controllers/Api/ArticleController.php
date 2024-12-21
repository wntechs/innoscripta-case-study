<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Search\ArticleSearch;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request){
        $articles = ArticleSearch::apply($request);

        return ArticleResource::collection($articles);
    }
}
