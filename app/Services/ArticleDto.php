<?php

namespace App\Services;


use App\Services\NewsApi\NewsApiService;
use Guardian\GuardianAPI;
use Illuminate\Support\Collection;

class ArticleDto
{


    public function __construct(public string $title,
    public string $content,
    public ?string $author,
    public string $publishedAt,
    public string $description,
    public string $url,
    public string $imageUrl)
    {

    }

    public static function fromNewsApi($article): ArticleDto
    {

        return new self(
          title: $article->title,
          content: $article->content,
          author: $article->author,
          publishedAt: $article->publishedAt,
          description: $article->description,
          url: $article->url,
          imageUrl: $article->urlToImage

        );
    }

    public static function fromGuardianApi($article): ArticleDto
    {
        return new self(
            title: $article->webTitle,
            content: $article->fields->body,
            author: self::parseGuardianAuthors($article->tags),
            publishedAt: $article->webPublicationDate,
            description: $article->fields->trailText,
            url: $article->webUrl,
            imageUrl: $article->fields->thumbnail

        );
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
            if($type === NewsApiService::AGGREGATOR_NAME) {
                $collection->add(self::fromNewsApi($article));
            }elseif($type === GuardianService::AGGREGATOR_NAME) {
                $collection->add(self::fromGuardianApi($article));
            }
        }
        return $collection;
    }

    private static function parseGuardianAuthors($tags){
        $authors = [];
        if(is_array($tags) && count($tags)){
            foreach ($tags as $tag){
                if($tag->type == 'contributor'){
                    $authors[] = $tag->webTitle;
                }
            }
        }
        return implode(', ',$authors);
    }
}
