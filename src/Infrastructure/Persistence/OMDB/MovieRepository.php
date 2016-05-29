<?php

namespace Fusani\Movies\Infrastructure\Persistence\OMDB;

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
        $this->movieBuilder = new Movie\MovieBuilder();
        $this->type = 'movie';
    }

    public function manyWithTitle($title)
    {
        $movies = [];

        $result = $this->adapter->get('', ['s' => $title.'*', 'r' => 'json', 'type' => $this->type]);

        if ($result['Response'] != 'False') {
            $title = strtolower(
                preg_replace('/\-(\-)+/', '-', preg_replace('/[^A-Za-z0-9]/', '-', $title))
            );

            foreach ($result['Search'] as $item) {
                $matchedTitle = strtolower(
                    preg_replace('/\-(\-)+/', '-', preg_replace('/[^A-Za-z0-9]/', '-', $item['Title']))
                );

                if ($matchedTitle == $title) {
                    $movies[] = $this->movieBuilder->buildFromOmdb($item);
                }
            }
        }

        return $movies;
    }

    public function manyWithTitleLike($title)
    {
        $movies = [];

        $result = $this->adapter->get('', ['s' => $title.'*', 'r' => 'json', 'type' => $this->type]);

        if ($result['Response'] != 'False') {
            foreach ($result['Search'] as $item) {
                $movies[] = $this->movieBuilder->buildFromOmdb($item);
            }
        }

        return $movies;
    }

    public function oneOfId($id)
    {
        return $this->oneOf(['i' => $id]);
    }

    public function oneOfTitle($title, $year = null)
    {
        $params = ['t' => $title, 'type' => $this->type];

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
            throw new Movie\NotFoundException('No movie was found.');
        }

        return $this->movieBuilder->buildFromOmdb($result);
    }

    public function searchForMovies()
    {
        $this->type = 'movie';
        return $this;
    }

    public function searchForShows()
    {
        $this->type = 'series';
        return $this;
    }

    public function withEpisodeDetails()
    {
        throw new NotYetImplementedException();
    }

    public function withoutEpisodeDetails()
    {
        // this function does nothing but is here to fit the interface definition
        return $this;
    }
}
