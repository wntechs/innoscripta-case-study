<?php

namespace App\Services\Dto;


trait HasNyTimesResponse
{
    public static function fromNyTimesApi($article): ArticleDto
    {

        return new self(
            title: $article->headline?->main,
            content: $article->lead_paragraph,
            author: self::parseNyTimesAuthors($article->byline),
            publishedAt: $article->pub_date,
            description: $article->abstract,
            url: $article->web_url,
            imageUrl: count($article->multimedia) ? $article->multimedia[0]->url : null,

        );
    }
    private static function parseNyTimesAuthors($byline)
    {
        $authors = [];
        if (isset($byline->person) && count($byline->person)) {
            foreach ($byline->person as $person) {
                $authors[] = implode(' ', [$person->firstname, $person->middlename, $person->lastname]);
            }
        }
        return implode(', ', $authors);
    }
}
