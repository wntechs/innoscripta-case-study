<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use jcobhams\NewsApi\NewsApi;

abstract class BaseService
{
    protected string $api_key;
    protected int $dailyApiLimit;
    protected string $keywords;
    protected string $language;
    const AGGREGATOR_NAME = 'news_api';
    const CACHE_KEY = "news_api_counts";
    const SORT_BY = 'publishedAt';
    const PAGE_SIZE = 100;

    public function __construct($config)
    {
        $api_key = $config['api_key'];
        if (empty($api_key)) {
            throw new \Exception('Please set api_key for the service');
        }
        $keywords = config('services.news_keywords');
        if (empty($keywords)) {
            throw new \Exception('Please set keywords for the service');
        } else {
            $this->keywords = str($keywords)->replace(',', ' OR ');
        }
        $this->dailyApiLimit = $config['daily_api_limit'];
        $this->language = config('services.news_lang');
        $this->api_key = $api_key;

    }
    /**
     * @return void
     * @throws DailyApiLimitException
     */
    protected function checkDailyApiThrottled(): void
    {
        // check if API daily limit is imposed or not
        if (!Cache::has(self::CACHE_KEY)) {
            Cache::add(self::CACHE_KEY, 0, now()->addHours(24));
        }
        $api_counts = Cache::get(self::CACHE_KEY, 0);
        if ($api_counts >= $this->dailyApiLimit) {
            throw new DailyApiLimitException('Daily API limit exceeded');
        }
    }
}
