<?php

namespace Fusani\Movies\Infrastructure\Persistence\OMDB;

use Fusani\Movies\Infrastructure\Adapter;

class MovieRepositoryFactory
{
    public function createRepository()
    {
        $adapter = new Adapter\GuzzleAdapter('http://www.omdbapi.com/');
        return new MovieRepository($adapter);
    }
}
