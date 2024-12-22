<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
        parent::__construct(config('services.news_api'));
        $sources = config('services.news_api.sources');
        if (empty($sources)) {
            throw new \Exception('Please set sources for the service');
        } else {
            $this->sources = $sources;
        }
        $this->newsApi = new NewsApi($this->api_key);
    }

    /**
     * @param $from string start date of the article
     * @param $to string end date of the article
     * @throws \jcobhams\NewsApi\NewsApiException
     * @return array
     */
    public function getArticles(Carbon $from, Carbon $to): array
    {

        $page = 1;
        $counter = 0;
        $articles = [];
        $hasMorePages = true; // assume that we will have more pages in the resultset in the start
        try{
            while ($hasMorePages) {
                $this->checkDailyApiThrottled();
                $results = $this->newsApi->getEverything(
                    q: $this->keywords,
                    sources: $this->sources,
                    from: $from->format('Y-m-d\TH:i:s'), to: $to->format('Y-m-d\TH:i:s'),
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

            }
        }catch (DailyApiLimitException $e){
            Log::error("ERR:".$e->getMessage());
        }catch (\Exception $e){
            Log::error("ERR2:".$e->getMessage());
        }

        return $articles;
    }

}
