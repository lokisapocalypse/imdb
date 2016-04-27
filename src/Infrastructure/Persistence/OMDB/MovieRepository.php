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
        return $this->oneOf(['i' => $id]);
    }

    public function oneOfTitle($title, $year = null)
    {
        $params = ['t' => $title];

        if ($year) {
            $params['y'] = $year;
        }

        return $this->oneOf($params);
    }

    private function oneOf(array $params)
    {
        $params = array_merge($params, ['r' => 'json']);

        $result = $this->adapter->get('', $params);

        if ($result['Response'] == 'False') {
            throw new NotFoundException('No movie was found.');
        }

        $movie = new Movie\Movie($result['imdbID']);
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
