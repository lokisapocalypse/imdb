<?php

namespace Fusani\Movies\Infrastructure\Persistence\Guidebox;

use Fusani\Movies\Domain\Model\Movie;
use Fusani\Movies\Infrastructure\Adapter;

class MovieRepository implements Movie\MovieRepository
{
    protected $adapter;
    protected $movieBuilder;
    protected $type;

    public function __construct(Adapter\Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->typeBuilder = new Movie\MovieBuilder();
        $this->type = 'movie';
    }

    protected function encode($str)
    {
        return urlencode(urlencode(urlencode($str)));
    }

    public function manyWithTitle($title)
    {
        $movies = [];
        $title = $this->encode($title);
        $result = $this->adapter->get("search/{$this->type}/title/$title/exact", []);

        foreach ($result['results'] as $movie) {
            $movies[] = $this->typeBuilder->buildFromGuidebox($movie);
        }

        return $movies;
    }

    public function manyWithTitleLike($title)
    {
        $movies = [];
        $title = $this->encode($title);
        $result = $this->adapter->get("search/{$this->type}/title/$title", []);

        foreach ($result['results'] as $movie) {
            $movies[] = $this->typeBuilder->buildFromGuidebox($movie);
        }

        return $movies;
    }

    public function oneOfId($id)
    {
        $id = $this->encode($id);
        $result = $this->adapter->get("{$this->type}/$id", []);

        if (empty($result)) {
            throw new Movie\NotFoundException('No movie was found.');
        }

        return $this->typeBuilder->buildFromGuidebox($result);
    }

    public function oneOfTitle($title, $year = null)
    {
        $movies = [];
        $title = $this->encode($title);

        $result = $this->adapter->get("search/{$this->type}/title/$title/exact", []);

        if (empty($year) && !empty($result['results'])) {
            return $this->typeBuilder->buildFromGuidebox($result['results'][0]);
        }

        foreach ($result['results'] as $movie) {
            if ($movie['release_year'] == $year) {
                return $this->typeBuilder->buildFromGuidebox($movie);
            }
        }

        throw new Movie\NotFoundException('No movie was found.');
    }

    public function searchForMovies()
    {
        $this->type = 'movie';
    }

    public function searchForShows()
    {
        $this->type = 'show';
    }
}
