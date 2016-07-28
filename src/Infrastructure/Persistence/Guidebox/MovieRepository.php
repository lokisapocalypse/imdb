<?php

namespace Fusani\Movies\Infrastructure\Persistence\Guidebox;

use DateTime;
use Fusani\Movies\Domain\Model\Movie;
use Fusani\Movies\Infrastructure\Adapter;

class MovieRepository implements Movie\MovieRepository
{
    protected $adapter;
    protected $episodeBuilder;
    protected $movieBuilder;
    protected $threshold;
    protected $titleSimilarityScoringService;
    protected $tryFuzzyOnFail;
    protected $type;

    public function __construct(Adapter\Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->episodeBuilder = new Movie\EpisodeBuilder();
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

    protected function encode($str)
    {
        return urlencode(urlencode(urlencode($str)));
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
        $id = $this->encode($id);

        $url = "show/$id/episodes/$season/$startAt/$limit/$sources/$platform/$includeLinks";
        $params = ['reverse_ordering' => $reverseOrder ? 'true' : 'false'];

        $result = $this->adapter->get($url, $params);

        foreach ($result['results'] as $episode) {
            $movie->addEpisode($this->episodeBuilder->buildFromGuideBox($episode));
        }

        return $movie;
    }

    public function manyWithTitle($title)
    {
        $movies = [];
        $title = $this->encode($title);
        $result = $this->adapter->get("search/{$this->type}/title/$title/exact", []);

        if (!empty($result['results'])) {
            foreach ($result['results'] as $movie) {
                $movies[] = $this->movieBuilder->buildFromGuidebox($movie);
            }
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

        $result = $this->adapter->get("{$this->type}/$id", []);

        if (empty($result)) {
            throw new Movie\NotFoundException('No movie was found.');
        }

        return $this->movieBuilder->buildFromGuidebox($result);
    }

    public function oneOfTitle($title, $year = null)
    {
        $movies = [];
        $encodedTitle = $this->encode($title);

        if ($this->type == 'movie') {
            $url = "search/{$this->type}/title/$encodedTitle/exact";
        } else {
            $url = "search/title/$encodedTitle/exact";
        }

        $result = $this->adapter->get($url, []);

        if (empty($year) && !empty($result['results'])) {
            return $this->movieBuilder->buildFromGuidebox($result['results'][0]);
        }

        foreach ($result['results'] as $movie) {
            if (!empty($movie['release_year']) && $movie['release_year'] == $year) {
                return $this->movieBuilder->buildFromGuidebox($movie);
            } elseif (!empty($movie['first_aired'])) {
                $firstAired = new DateTime($movie['first_aired']);

                if ($firstAired->format('Y') == $year) {
                    return $this->movieBuilder->buildFromGuidebox($movie);
                }
            }
        }

        if ($this->tryFuzzyOnFail) {
            $movies = $this->manyWithTitleLike($title);
            $result = $this->titleSimilarityScoringService->findClosestMatch($title, $movies);

            if ($result['score'] <= $this->threshold) {
                return $result['movie'];
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
