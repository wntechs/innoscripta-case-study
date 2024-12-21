<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NyTimeService extends BaseService
{
    const AGGREGATOR_NAME = 'nytimes_api';
    const CACHE_KEY = "nytimes_api_counts";
    const SORT_BY = 'newest';
    const PAGE_SIZE = 10;
    protected $api_key;
    protected $rate_per_minute;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $api_key = config('services.nytimes_api.api_key');
        if (empty($api_key)) {
            throw new \Exception('Please set api_key for the service');
        }
        $this->api_key = $api_key;

        $keywords = config('services.nytimes_api.keywords');
        if (empty($keywords)) {
            throw new \Exception('Please set keywords for the service');
        } else {
            $this->keywords = str($keywords)->replace(',', ' OR ');
        }

        $this->dailyApiLimit = config('services.nytimes_api.daily_api_limit', 100);
        $this->rate_per_minute = config('services.nytimes_api.api_rate_per_minute', 10);
        $this->language = config('services.nytimes_api.language', 'en');
    }

    /**
     * @param $from string start date of the article
     * @param $to string end date of the article
     * @return array
     * @throws \Exception
     */
    public function getArticles($from, $to, $page = 1): array
    {


        $articles = [];
        $hasMorePages = true; // assume that we will have more pages in the resultset in the start

        try{
            while ($hasMorePages) {
                $this->checkDailyApiThrottled();
                $response = Http::get('https://api.nytimes.com/svc/search/v2/articlesearch.json', [
                    'fq' => $this->keywords,
                    'page' => $page,
                    'api-key' => $this->api_key,
                    'sort' => self::SORT_BY,
                    'begin_date' => Carbon::parse($from)->format('Ymd'),
                    'end_date' => Carbon::parse($to)->format('Ymd'),
                ]);
                $results = json_decode($response->body());

                if (isset($results->status) && $results->status == 'OK') {
                    Cache::increment(self::CACHE_KEY);

                    $total = $results->response->meta->hits;
                    // determine if there are more pages
                    $total_pages = ceil($total / self::PAGE_SIZE);
                    if ($page >= $total_pages) {
                        $hasMorePages = false;
                    }
                    $page++;
                    if (is_array($results->response->docs) && count($results->response->docs) > 0) {

                        $articles = array_merge($results->response->docs, $articles);

                    }
                }
                //$hasMorePages = false;
            }
        }catch (DailyApiLimitException $e){
            Log::error($e->getMessage());
        }
        return $articles;
    }
}
