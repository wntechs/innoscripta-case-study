<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\ArticleDto;

use App\Services\GuardianService;
use App\Services\NewsApi\NewsApiService;
use App\Services\NyTimeService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-articles {source : The source aggregator name}';

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
        $aggregator = $this->argument('source');
        $validate_aggregators = [
            NewsApiService::AGGREGATOR_NAME, GuardianService::AGGREGATOR_NAME, NyTimeService::AGGREGATOR_NAME
        ];
        if (!in_array($aggregator, $validate_aggregators)) {
            $this->error('Invalid Aggregator');
            return;
        }
        if (Article::query()->count() > 0) {
            $from = Article::query()->where('aggregator', $aggregator)
                ->max('published_at');
        } else {
            $from = Carbon::now()->subHours(24);
        }
        //$from = Carbon::now()->subHours(24);
        $to = Carbon::now();

        try {
            switch ($aggregator) {
                case NewsApiService::AGGREGATOR_NAME:
                    $results = $this->getNewsApiArticles($from, $to);
                    break;
                case GuardianService::AGGREGATOR_NAME:
                    $results = $this->getGuardianArticles($from, $to);
                    break;
                case NyTimeService::AGGREGATOR_NAME:
                    $results = $this->getNyTimeArticles($from, $to);
            }
            foreach ($results as $result) {
                Article::query()->updateOrCreate(['external_url' => $result->url],
                    [
                        'title' => $result->title,
                        'description' => $result->description,
                        'content' => $result->content,
                        'image_url' => $result->imageUrl,
                        'published_at' => $result->publishedAt,
                        'author' => $result->author,
                        'aggregator' => $aggregator,
                    ]);
            }

        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    private function getNewsApiArticles($from, $to)
    {
        $newsApiService = new NewsApiService();
        return ArticleDto::collection($newsApiService->getArticles($from, $to), NewsApiService::AGGREGATOR_NAME);
    }

    private function getGuardianArticles($from, $to)
    {
        $apiService = new GuardianService();
        return ArticleDto::collection($apiService->getArticles($from, $to), GuardianService::AGGREGATOR_NAME);
    }
    private function getNyTimeArticles($from, $to)
    {
        $apiService = new NyTimeService();
        return ArticleDto::collection($apiService->getArticles($from, $to), NyTimeService::AGGREGATOR_NAME);
    }
}
