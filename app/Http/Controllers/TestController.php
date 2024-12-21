<?php

namespace App\Http\Controllers;



use App\Services\ArticleDto;

class TestController extends Controller
{

    public function test(){
        $json = json_decode(file_get_contents(storage_path("app/guardian.json")), false);
        /*$data = NewsApiData::collect($json->articles);
        dd($data->toArray());*/
        //dd($json->results[0]->tags[0] ?? $json->results[0]->tags[0]->webTitle);
            //$article->tags[0] ?? $article->tags[0]->webTitle
        $data = ArticleDto::fromGuardianApi($json->results[0]);
        dd($data);
    }
}
