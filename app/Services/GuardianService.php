<?php

namespace App\Services;

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
        $api_key = config('services.guardian_api.api_key');
        if (empty($api_key)) {
            throw new \Exception('Please set api_key for the service');
        }

        $keywords = config('services.guardian_api.keywords');
        if (empty($keywords)) {
            throw new \Exception('Please set keywords for the service');
        } else {
            $this->keywords = str($keywords)->replace(',', ' OR ');
        }

        $this->dailyApiLimit = config('services.guardian_api.daily_api_limit', 100);
        $this->language = config('services.guardian_api.language', 'en');
        $this->api = new GuardianAPI($api_key);
    }
    /**
     * @param $from string start date of the article
     * @param $to string end date of the article
     * @return array
     * @throws \Exception
     */
    public function getArticles($from, $to, $page = 1): array
    {

        $this->checkApiThrottled();
        $articles = [];
        $hasMorePages = true; // assume that we will have more pages in the resultset in the start

        while ($hasMorePages) {
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
                    //Log::debug("guardian", [$results->response->results]);
                    $articles = array_merge($results->response->results, $articles);

                }
            }
            //$hasMorePages = false;
        }
        return $articles;
    }
}
