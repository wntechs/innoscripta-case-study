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
    protected $rate_per_minute;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct(config('services.nytimes_api'));
        $this->rate_per_minute = config('services.nytimes_api.api_rate_per_minute', 10);

    }

    /**
     * @param Carbon $from
     * @param Carbon $to
     * @param $page
     * @return array
     */
    public function getArticles(Carbon $from, Carbon $to, $page = 1): array
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
                    'begin_date' => $from->format('Ymd'),
                    'end_date' => $to->format('Ymd'),
                ]);
                $results = json_decode($response->body());
                if(isset($results->fault)){
                    Log::error("Api Limit Reached", [$results]);
                    break;
                }
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

                        sleep(10); //to prevent rate limit per minutes
                    }
                }
                //$hasMorePages = false;
            }
        }catch (DailyApiLimitException $e){
            Log::error($e->getMessage());
        }catch (\Exception $e){
            Log::error("ERR2:".$e->getMessage());
        }
        return $articles;
    }
}
