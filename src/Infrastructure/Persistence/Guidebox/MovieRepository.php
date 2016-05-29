<?php

namespace Fusani\Movies\Infrastructure\Persistence\Guidebox;

use DateTime;
use Fusani\Movies\Domain\Model\Movie;
use Fusani\Movies\Infrastructure\Adapter;

class MovieRepository implements Movie\MovieRepository
{
    protected $adapter;
    protected $includeEpisodeDetails;
    protected $movieBuilder;
    protected $type;

    public function __construct(Adapter\Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->includeEpisodeDetails = false;
        $this->movieBuilder = new Movie\MovieBuilder();
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
            $movies[] = $this->movieBuilder->buildFromGuidebox($movie);
        }

        return $movies;
    }

    public function manyWithTitleLike($title)
    {
        $movies = [];
        $title = $this->encode($title);
        $result = $this->adapter->get("search/{$this->type}/title/$title", []);

        foreach ($result['results'] as $movie) {
            $movies[] = $this->movieBuilder->buildFromGuidebox($movie);
        }

        return $movies;
    }

    public function oneOfId($id)
    {
        $id = $this->encode($id);

        $url = "{$this->type}/$id";

        if ($this->type == 'show' && $this->includeEpisodeDetails) {
            $url .= '/episodes/all/0/25/all/all/true';
        }

        $result = $this->adapter->get($url, []);

        if (empty($result)) {
            throw new Movie\NotFoundException('No movie was found.');
        }

        return $this->movieBuilder->buildFromGuidebox($result);
    }

    public function oneOfTitle($title, $year = null)
    {
        $movies = [];
        $title = $this->encode($title);

        if ($this->type == 'movie') {
            $url = "search/{$this->type}/title/$title/exact";
        } else {
            $url = "search/title/$title/exact";
        }

        $result = $this->adapter->get($url, []);

        if (empty($year) && !empty($result['results'])) {
            return $this->movieBuilder->buildFromGuidebox($result['results'][0]);
        }

        foreach ($result['results'] as $movie) {
            if (!empty($movie['release_year']) && $movie['release_year'] == $year) {
                return $this->movieBuilder->buildFromGuidebox($movie);
            } else if (!empty($movie['first_aired'])) {
                $firstAired = new DateTime($movie['first_aired']);

                if ($firstAired->format('Y') == $year) {
                    return $this->movieBuilder->buildFromGuidebox($movie);
                }
            }
        }

        throw new Movie\NotFoundException('No movie was found.');
    }

    public function searchForMovies()
    {
        $this->type = 'movie';
        return $this;
    }

    public function searchForShows()
    {
        $this->type = 'show';
        return $this;
    }

    public function withEpisodeDetails()
    {
        $this->includeEpisodeDetails = true;
        return $this;
    }

    public function withoutEpisodeDetails()
    {
        $this->includeEpisodeDetails = false;
        return $this;
    }
}
