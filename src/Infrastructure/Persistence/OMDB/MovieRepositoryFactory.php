<?php

namespace Fusani\Omdb\Infrastructure\Persistence\OMDB;

use Fusani\Omdb\Infrastructure\Adapter;

class MovieRepositoryFactory
{
    public function createRepository()
    {
        $adapter = new Adapter\GuzzleAdapter('http://www.omdbapi.com/');
        return new MovieRepository($adapter);
    }
}
