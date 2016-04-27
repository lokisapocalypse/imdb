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
        $movie = new Movie\Movie($id);

        $result = $this->adapter->get('', ['i' => $id, 'r' => 'json']);

        if ($result['Response'] == 'False') {
            throw new NotFoundException('No movie was found.');
        }

        $movie->populate(
            $result['Plot'],
            $result['Poster'] == 'N/A' ? null : $result['Poster'],
            $result['Title'],
            $result['Type'],
            $result['Year']
        );

        return $movie;
    }
}
