<?php

namespace App\Services\Dto;


trait HasNewsApiResponse
{
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
}
