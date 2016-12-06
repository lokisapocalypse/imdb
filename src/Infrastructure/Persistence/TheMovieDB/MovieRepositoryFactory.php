<?php

namespace Fusani\Movies\Infrastructure\Persistence\TheMovieDB;

use Fusani\Movies\Infrastructure\Adapter;

class MovieRepositoryFactory
{
    public function createRepository($apikey, $version = 3, $language = 'en-US')
    {
        $adapter = new Adapter\GuzzleAdapter("https://api.themoviedb.org/$version/");
        return new MovieRepository($adapter, $apikey, $language);
    }
}
