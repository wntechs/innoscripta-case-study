<?php

namespace App\Services\NewsApi;

use App\Services\BaseService;
use Illuminate\Support\Facades\Cache;
use jcobhams\NewsApi\NewsApi;

class NewsApiService extends BaseService
{
    protected NewsApi $newsApi;
    protected string $sources;
    const AGGREGATOR_NAME = 'news_api';
    const CACHE_KEY = "news_api_counts";
    const SORT_BY = 'publishedAt';
    const PAGE_SIZE = 100;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $api_key = config('services.news_api.api_key');
        if (empty($api_key)) {
            throw new \Exception('Please set api_key for the service');
        }

        $keywords = config('services.news_api.keywords');
        if (empty($keywords)) {
            throw new \Exception('Please set keywords for the service');
        } else {
            $this->keywords = str($keywords)->replace(',', ' OR ');
        }

        $sources = config('services.news_api.sources');
        if (empty($sources)) {
            throw new \Exception('Please set sources for the service');
        } else {
            $this->sources = $sources;
        }
        $this->dailyApiLimit = config('services.news_api.daily_api_limit', 100);
        $this->language = config('services.news_api.language', 'en');
        $this->newsApi = new NewsApi($api_key);
    }

    /**
     * @param $from string start date of the article
     * @param $to string end date of the article
     * @return array
     * @throws \jcobhams\NewsApi\NewsApiException
     * @throws \Exception
     */
    public function getArticles($from, $to): array
    {
        $this->checkApiThrottled();
        $articles = [];
        $hasMorePages = true; // assume that we will have more pages in the resultset in the start

        while ($hasMorePages) {
            $results = $this->newsApi->getEverything(
                q: $this->keywords,
                sources: $this->sources,
                from: $from, to: $to,
                language: $this->language,
                sort_by: self::SORT_BY,
                page_size: self::PAGE_SIZE,
                page: $page,
            );
            if ($results->status == 'ok') {
                Cache::increment(self::CACHE_KEY);
                $total = $results->totalResults;
                // determine if there are more pages
                $total_pages = ceil($total / self::PAGE_SIZE);
                if ($page >= $total_pages) {
                    $hasMorePages = false;
                }
                $page++;
                if (is_array($results->articles) && count($results->articles) > 0) {
                    $articles = array_merge($results->articles, $articles);
                }
            }
            //$hasMorePages = false;
        }
        return $articles;
    }

}
