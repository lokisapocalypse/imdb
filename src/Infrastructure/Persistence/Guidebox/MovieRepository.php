<?php

namespace Fusani\Movies\Infrastructure\Persistence\Guidebox;

use DateTime;
use Fusani\Movies\Domain\Model\Movie;
use Fusani\Movies\Infrastructure\Adapter;

class MovieRepository implements Movie\MovieRepository
{
    const UPDATE_MAX = 1000;

    protected $adapter;
    protected $episodeBuilder;
    protected $movieBuilder;
    protected $threshold;
    protected $titleSimilarityScoringService;
    protected $tryFuzzyOnFail;
    protected $type;
    protected $update;

    public function __construct(Adapter\Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->episodeBuilder = new Movie\EpisodeBuilder();
        $this->movieBuilder = new Movie\MovieBuilder();
        $this->threshold = 0;
        $this->titleSimilarityScoringService = new Movie\TitleSimilarityScoringService();
        $this->tryFuzzyOnFail = false;
        $this->type = 'movie';
        $this->update = 'new';
    }

    public function currentTime()
    {
        $result = $this->adapter->get('updates/get_current_time', []);
        return $result['results'];
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

    public function many($startAt, $numRecords, $type)
    {
        $movies = [];
        $url = "$type/all/$startAt/$numRecords/all/all";
        $result = $this->adapter->get($url, []);

        foreach ($result['results'] as $data) {
            $movies[] = $this->movieBuilder->buildFromGuidebox($data, $type);
        }

        return $movies;
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

    public function manyMoviesWithChanges($time)
    {
        $movies = [];
        $params = ['limit' => self::UPDATE_MAX];

        $totalPages = 1;

        for ($page = 1; $page <= $totalPages; $page++) {
            $params['page'] = $page;

            // guidebox requires a delay for consecutive calls
            // i've tried avoid doing that here but this function necessitates it
            if ($page > 1) {
                sleep(1);
            }

            // the extra s is intentional as it requires the plural version of the word
            $result = $this->adapter->get("updates/{$this->type}s/{$this->update}/$time", $params);

            if (!empty($result['total_pages'])) {
                $totalPages = $result['total_pages'];
            }

            // these aren't converted to objects because they are just ids and timestamps
            if (!empty($result['results'])) {
                $movies = array_merge($movies, $result['results']);
            }
        }

        return $movies;
    }

    public function manyWithTitle($title)
    {
        $movies = [];
        $title = $this->encode($title);

        if ($this->type == 'movie') {
            $url = "search/{$this->type}/title/$title/exact";
        } else {
            $url = "search/title/$title/exact";
        }

        $result = $this->adapter->get($url, []);

        if (!empty($result['results'])) {
            foreach ($result['results'] as $movie) {
                $movies[] = $this->movieBuilder->buildFromGuidebox($movie, $this->type);
            }
        }

        return $movies;
    }

    public function manyWithTitleLike($title)
    {
        $movies = [];
        $title = $this->encode($title);

        if ($this->type == 'movie') {
            $url = "search/{$this->type}/title/$title/fuzzy";
        } else {
            $url = "search/title/$title/fuzzy";
        }

        $result = $this->adapter->get($url, []);

        foreach ($result['results'] as $movie) {
            $movies[] = $this->movieBuilder->buildFromGuidebox($movie, $this->type);
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

        return $this->movieBuilder->buildFromGuidebox($result, $this->type);
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
            return $this->movieBuilder->buildFromGuidebox($result['results'][0], $this->type);
        }

        foreach ($result['results'] as $movie) {
            if (!empty($movie['release_year']) && $movie['release_year'] == $year) {
                return $this->movieBuilder->buildFromGuidebox($movie, $this->type);
            } elseif (!empty($movie['first_aired'])) {
                $firstAired = new DateTime($movie['first_aired']);

                if ($firstAired->format('Y') == $year) {
                    return $this->movieBuilder->buildFromGuidebox($movie, $this->type);
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

    public function withAlternateTitles()
    {
        return $this;
    }

    public function withAllData()
    {
        return $this;
    }

    public function withCast()
    {
        return $this;
    }

    public function withKeywords()
    {
        return $this;
    }

    public function withNewEpisodes()
    {
        $this->update = 'new_episodes';
        return $this;
    }

    public function withNewMovies()
    {
        $this->update = 'new';
        return $this;
    }

    public function withRecommendations()
    {
        return $this;
    }

    public function withReviews()
    {
        return $this;
    }

    public function withSimilarMovies()
    {
        return $this;
    }

    public function withUpdatedEpisodes()
    {
        $this->update = 'changed_episodes';
        return $this;
    }

    public function withUpdatedMovies()
    {
        $this->update = 'changes';
        return $this;
    }
}
