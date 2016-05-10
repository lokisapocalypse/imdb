<?php

namespace Fusani\Movies\Infrastructure\Persistence\Guidebox;

use DateTime;
use Fusani\Movies\Domain\Model\Movie;
use Fusani\Movies\Infrastructure\Adapter;

class MovieRepository implements Movie\MovieRepository
{
    protected $adapter;
    protected $movieBuilder;

    public function __construct(Adapter\Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->movieBuilder = new Movie\MovieBuilder();
    }

    public function manyWithTitleLike($title)
    {
        $movies = [];
        $result = $this->adapter->get("search/title/$title", []);

        foreach ($result['results'] as $movie) {
            $movies[] = $this->movieBuilder->buildFromGuidebox($movie);
        }

        return $movies;
    }

    public function oneOfId($id)
    {
        $result = $this->adapter->get("movie/$id", []);

        if (empty($result)) {
            throw new Movie\NotFoundException('No movie was found.');
        }

        return $this->movieBuilder->buildFromGuidebox($result);
    }

    public function oneOfTitle($title, $year = null)
    {
        $movies = [];
        $result = $this->adapter->get("search/movie/title/$title/exact", []);

        if (empty($year) && !empty($result['results'])) {
            return $this->movieBuilder->buildFromGuidebox($result['results'][0]);
        }

        foreach ($result['results'] as $movie) {
            if ($movie['release_year'] == $year) {
                return $this->movieBuilder->buildFromGuidebox($movie);
            }
        }

        throw new Movie\NotFoundException('No movie was found.');
    }
}
