<?php

namespace App\Services\Dto;

use App\Services\GuardianService;
use App\Services\NewsApiService;
use App\Services\NyTimeService;
use Illuminate\Support\Collection;

class ArticleDto
{
    use HasNewsApiResponse, HasGuardianResponse, HasNyTimesResponse;

    public function __construct(public string  $title,
                                public string  $content,
                                public ?string $author,
                                public string  $publishedAt,
                                public ?string  $description,
                                public string  $url,
                                public ?string  $imageUrl)
    {

    }

    /**
     * @param $articles
     * @param string $type
     * @return Collection<ArticleDto>
     */
    public static function collection($articles, string $type = 'news_api')
    {
        $collection = collect();
        foreach ($articles as $article) {
            if ($type === NewsApiService::AGGREGATOR_NAME) {
                $collection->add(self::fromNewsApi($article));
            } elseif ($type === GuardianService::AGGREGATOR_NAME) {
                $collection->add(self::fromGuardianApi($article));
            } elseif ($type === NyTimeService::AGGREGATOR_NAME) {
                $collection->add(self::fromNyTimesApi($article));
            }
        }
        return $collection;
    }
}
