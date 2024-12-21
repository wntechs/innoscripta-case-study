<?php

namespace App\Services\Dto;


trait HasGuardianResponse
{
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

    private static function parseGuardianAuthors($tags)
    {
        $authors = [];
        if (is_array($tags) && count($tags)) {
            foreach ($tags as $tag) {
                if ($tag->type == 'contributor') {
                    $authors[] = $tag->webTitle;
                }
            }
        }
        return implode(', ', $authors);
    }
}
