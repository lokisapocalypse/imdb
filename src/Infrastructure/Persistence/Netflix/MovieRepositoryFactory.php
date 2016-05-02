<?php

namespace Fusani\Movies\Infrastructure\Persistence\Netflix;

use Fusani\Movies\Infrastructure\Adapter;

class MovieRepositoryFactory
{
    public function createRepository()
    {
        $adapter = new Adapter\GuzzleAdapter('http://netflixroulette.net/api/api.php');
        return new MovieRepository($adapter);
    }
}
