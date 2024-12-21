<?php

namespace App\Search;

use App\Models\Article;
use App\Services\GuardianService;
use App\Services\NewsApi\NewsApiService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ArticleSearch
{
    public static function apply(Request $filters)
    {
        $article = (new Article)->newQuery();
        if ($filters->has('q')) {
            $article->where(function ($query) use ($filters) {
                $query->where('title', 'like', '%'. $filters->input('q') .'%')
                ->orWhere('content', 'like', '%'. $filters->input('q') .'%')
                ->orWhere('description', 'like', '%'. $filters->input('q') .'%');
            });
        }

        if ($filters->has('author')) {
            $article->where('author', 'like', '%'. $filters->input('author') .'%');
        }
        if ($filters->has('date')) {
            $date = Carbon::parse($filters->input('date'));
            $article->whereDate('published_at',  $date);
        }

        if ($filters->has('min_date')) {
            $min_date = Carbon::parse($filters->input('min_date'));
            $article->whereDate('published_at', '>=',  $min_date);
        }

        if ($filters->has('max_date')) {
            $max_date = Carbon::parse($filters->input('max_date'));
            $article->whereDate('published_at', '<=',  $max_date);
        }

        if( $filters->has('source') ){
            $validate_source = [NewsApiService::AGGREGATOR_NAME, GuardianService::AGGREGATOR_NAME];
            if( in_array($filters->input('source'), $validate_source) ){
                $article->where('source', $filters->input('source') );
            }
        }

        return $article->paginate();
    }
}
