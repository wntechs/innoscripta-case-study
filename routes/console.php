<?php

use App\Console\Commands\SyncArticles;

use Illuminate\Support\Facades\Schedule;


Schedule::command(SyncArticles::class, ['news_api'])->daily()->at('12:10');
Schedule::command(SyncArticles::class, ['guardian_api'])->daily()->at('12:20');
Schedule::command(SyncArticles::class, ['nytimes_api'])->daily()->at('12:30');
