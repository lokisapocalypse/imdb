<?php

namespace Fusani\Movies\Infrastructure\Persistence\Guidebox;

use Fusani\Movies\Infrastructure\Adapter;

class MovieRepositoryFactory
{
    public function createRepository($apikey, $region = 'US')
    {
        $adapter = new Adapter\GuzzleAdapter("http://api-public.guidebox.com/v1.43/$region/$apikey/");
        return new MovieRepository($adapter);
    }
}
