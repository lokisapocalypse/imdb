<?php

namespace Fusani\Movies\Infrastructure\Persistence\OMDB;

use Fusani\Movies\Domain\Model\Movie;
use Fusani\Movies\Infrastructure\Adapter;

class MovieRepository implements Movie\MovieRepository
{
    protected $adapter;
    protected $movieBuilder;
    protected $threshold;
    protected $titleSimilarityScoringService;
    protected $tryFuzzyOnFail;
    protected $type;

    public function __construct(Adapter\Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->movieBuilder = new Movie\MovieBuilder();
        $this->threshold = 0;
        $this->titleSimilarityScoringService = new Movie\TitleSimilarityScoringService();
        $this->tryFuzzyOnFail = false;
        $this->type = 'movie';
    }

    public function doNotTryFuzzyOnFail()
    {
        $this->tryFuzzyOnFail = false;
        return $this;
    }

    public function manyEpisodesOfShow(
        Movie\Movie $movie,
        $id,
        $includeLinks = false,
        $reverseOrder = false,
        $season = 'all',
        $startAt = 0,
        $limit = 25,
        $sources = 'all',
        $platform = 'all'
    ) {
        throw new NotYetImplementedException();
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

        try {
            return $this->oneOf($params);
        } catch (Movie\NotFoundException $e) {
            if ($this->tryFuzzyOnFail) {
                $movies = $this->manyWithTitleLike($title);
                $result = $this->titleSimilarityScoringService->findClosestMatch($title, $movies);

                if ($result['score'] <= $this->threshold) {
                    return $result['movie'];
                }
            }

            throw $e;
        }
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

    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;
        return $this;
    }

    public function tryFuzzyOnFail()
    {
        $this->tryFuzzyOnFail = true;
        return $this;
    }
}
