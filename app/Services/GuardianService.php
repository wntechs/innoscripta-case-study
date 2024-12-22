<?php

namespace App\Services;

use Carbon\Carbon;
use Guardian\GuardianAPI;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class GuardianService extends BaseService
{
    protected GuardianAPI $api;
    const AGGREGATOR_NAME = 'guardian_api';
    const CACHE_KEY = "guardian_api_counts";
    const SORT_BY = 'publishedAt';
    const PAGE_SIZE = 50;
    /**
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct(config('services.guardian_api'));

        $this->api = new GuardianAPI($this->api_key);
    }

    /**
     * @param Carbon $from
     * @param Carbon $to
     * @param int $page
     * @return array
     * @throws \DateMalformedStringException
     */
    public function getArticles(Carbon $from, Carbon $to, int $page = 1): array
    {
        //dd($from->format('Y-m-d'), $to->format('Y-m-d'));
        $articles = [];
        $hasMorePages = true; // assume that we will have more pages in the resultset in the start
        try{
            while ($hasMorePages) {

                $this->checkDailyApiThrottled();

                $results = $this->api->content()->setQuery($this->keywords)
                    ->setLang($this->language)
                    ->setShowFields('body,thumbnail,trailText')
                    ->setShowTags('contributor')
                    ->setPage($page)
                    ->setPageSize(self::PAGE_SIZE)
                    ->setFromDate(new \DateTimeImmutable($from))
                    ->setToDate(new \DateTimeImmutable($to))
                    ->fetch();

                if (isset($results->response) && $results->response->status == 'ok') {
                    Cache::increment(self::CACHE_KEY);

                    // determine if there are more pages
                    $total_pages = $results->response->pages;
                    if ($page > $total_pages) {
                        $hasMorePages = false;
                    }
                    $page++;
                    if (is_array($results->response->results) && count($results->response->results) > 0) {
                        $articles = array_merge($results->response->results, $articles);

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
