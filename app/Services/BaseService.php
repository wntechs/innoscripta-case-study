<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

abstract class BaseService
{
    protected int $dailyApiLimit;
    protected string $keywords;
    protected string $language;
    const AGGREGATOR_NAME = 'news_api';
    const CACHE_KEY = "news_api_counts";
    const SORT_BY = 'publishedAt';
    const PAGE_SIZE = 100;

    /**
     * @throws \Exception
     */
    protected function checkApiThrottled(): void
    {
        // check if API daily limit is imposed or not
        if (!Cache::has(self::CACHE_KEY)) {
            Cache::add(self::CACHE_KEY, 0, now()->addHours(24));
        }
        $api_counts = Cache::get(self::CACHE_KEY, 0);
        if ($api_counts >= $this->dailyApiLimit) {
            throw new \Exception('Daily API limit exceeded');
        }
    }
}
