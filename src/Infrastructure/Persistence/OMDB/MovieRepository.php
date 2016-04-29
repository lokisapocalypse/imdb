<?php

namespace Fusani\Movies\Infrastructure\Persistence\OMDB;

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

        $result = $this->adapter->get('', ['s' => $title.'*', 'r' => 'json']);

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

        return $this->movieBuilder->buildFromOmdb($result);
    }
}
