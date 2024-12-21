<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\ArticleDto;

use App\Services\GuardianService;
use App\Services\NewsApi\NewsApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (Article::query()->count() > 0) {
            $from = Article::query()->where('aggregator', NewsApiService::AGGREGATOR_NAME)
                ->max('published_at');
        } else {
            $from = Carbon::now()->subHours(24);
        }
        $from = Carbon::now()->subHours(24);
        $to = Carbon::now();
        $results = $this->getGuardianArticles($from, $to);
        /*$results = $this->getNewsApiArticles($from, $to);*/
        foreach ($results as $result) {
            Article::query()->updateOrCreate(['external_url' => $result->url],
                [
                    'title' => $result->title,
                    'description' => $result->description,
                    'content' => $result->content,
                    'image_url' => $result->imageUrl,
                    'published_at' => $result->publishedAt,
                    'author' => $result->author,
                    'aggregator' => GuardianService::AGGREGATOR_NAME,
                ]);
        }
        try {


        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    private function getNewsApiArticles($from, $to)
    {
        $newsApiService = new NewsApiService();
        return ArticleDto::collection($newsApiService->getArticles($from, $to));
    }

    private function getGuardianArticles($from, $to){
        $apiService = new GuardianService();
        return ArticleDto::collection($apiService->getArticles($from, $to), 'guardian_api');
    }
}