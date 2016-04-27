<?php

namespace Fusani\Movies\Infrastructure\Persistence\OMDB;

use Fusani\Movies\Domain\Model\Movie;
use Fusani\Movies\Infrastructure\Adapter;

class MovieRepository implements Movie\MovieRepository
{
    protected $adapter;

    public function __construct(Adapter\Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function manyWithTitleLike($title)
    {
        throw new NotYetImplementedException('Not yet implemented.');
    }

    public function oneOfId($id)
    {
        throw new NotYetImplementedException('Not yet implemented.');
    }
}
