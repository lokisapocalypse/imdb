<?php

namespace Fusani\Movies\Infrastructure\Persistence\TheMovieDB;

use Fusani\Movies\Domain\Model\Movie;
use Fusani\Movies\Infrastructure\Adapter;

class MovieRepository implements Movie\MovieRepository
{
    protected $adapter;
    protected $apikey;
    protected $defaultParams;
    protected $episodeBuilder;
    protected $language;
    protected $movieBuilder;
    protected $skipAllMetadata;
    protected $threshold;
    protected $titleSimilarityScoringService;
    protected $tryFuzzyOnFail;
    protected $type;
    protected $update;
    protected $withAlternateTitles;
    protected $withCast;
    protected $withKeywords;
    protected $withRecommendations;
    protected $withSimilarMovies;
    protected $withReviews;

    public function __construct(Adapter\Adapter $adapter, $apikey, $language)
    {
        $this->adapter = $adapter;
        $this->apikey = $apikey;

        $this->defaultParams = [
            'api_key' => $apikey,
            'language' => $language,
        ];

        $this->episodeBuilder = new Movie\EpisodeBuilder();
        $this->language = $language;
        $this->movieBuilder = new Movie\MovieBuilder();
        $this->skipAllMetadata = false;
        $this->threshold = 0;
        $this->titleSimilarityScoringService = new Movie\TitleSimilarityScoringService();
        $this->tryFuzzyOnFail = false;
        $this->type = 'movie';
        $this->update = 'new';
        $this->withAlternateTitles = false;
        $this->withCast = false;
        $this->withKeywords = false;
        $this->withRecommendations = false;
        $this->withSimilarMovies = false;
        $this->withReviews = false;
    }

    protected function addAdditionalMetadata(Movie\Movie $movie)
    {
        if ($this->skipAllMetadata) {
            return $movie;
        }

        if ($this->withAlternateTitles) {
            $movie = $this->manyAlternateTitlesOfMovie($movie);
        }

        if ($this->withCast) {
            $movie = $this->manyCastOfMovie($movie);
        }

        if ($this->withKeywords) {
            $movie = $this->manyKeywordsOfMovie($movie);
        }

        if ($this->withRecommendations) {
            $movie = $this->manyRecommendationsOfMovie($movie);
        }

        if ($this->withReviews) {
            $movie = $this->manyReviewsOfMovie($movie);
        }

        if ($this->withSimilarMovies) {
            $movie = $this->manySimilarMoviesOfMovie($movie);
        }

        return $movie;
    }

    public function currentTime()
    {
        return time();
    }

    public function doNotTryFuzzyOnFail()
    {
        $this->tryFuzzyOnFail = false;
        return $this;
    }

    public function many($startAt, $numRecords, $type)
    {
        $movies = [];

        if ($type == 'movie') {
            $this->searchForMovies();
        } elseif ($type == 'tv') {
            $this->searchForShows();
        }

        for ($i = $startAt; $i < $numRecords + $startAt; $i++) {
            try {
                $movies[] = $this->oneOfId($i);
            } catch (Movie\NotFoundException $e) {
                // ignore exception and continue
            }
        }

        return $movies;
    }

    protected function manyAlternateTitlesOfMovie(Movie\Movie $movie)
    {
        $id = $movie->identity();
        $result = $this->adapter->get("{$this->type}/$id/alternate_titles", $this->defaultParams);

        if (!empty($result['titles'])) {
            return $this->movieBuilder->addAlternateTitlesFromTheMovieDB($movie, $result['titles']);
        }

        return $movie;
    }

    protected function manyCastOfMovie(Movie\Movie $movie)
    {
        $id = $movie->identity();
        $result = $this->adapter->get("{$this->type}/$id/credits", $this->defaultParams);

        if (!empty($result['cast']) || !empty($result['crew'])) {
            return $this->movieBuilder->addCastFromTheMovieDB($movie, $result);
        }

        return $movie;
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
        $identity = $movie->identity();

        for ($i = $startAt; $i <= $limit; $i++) {
            $result = $this->adapter->get("tv/$identity/season/$season/episode/$i", $this->defaultParams);

            if (empty($result['status_code']) || $result['status_code'] != 34) {
                $movie->addEpisode($this->episodeBuilder->buildFromTheMovieDB($result));
            }
        }

        return $movie;
    }

    protected function manyKeywordsOfMovie(Movie\Movie $movie)
    {
        $id = $movie->identity();
        $result = $this->adapter->get("{$this->type}/$id/keywords", $this->defaultParams);

        if (!empty($result['keywords'])) {
            return $this->movieBuilder->addKeywordsFromTheMovieDB($movie, $result['keywords']);
        }

        return $movie;
    }

    public function manyMoviesWithChanges($time)
    {
        throw new NotYetImplementedException();
    }

    protected function manyRecommendationsOfMovie(Movie\Movie $movie)
    {
        $id = $movie->identity();
        $result = $this->adapter->get("{$this->type}/$id/recommendations", $this->defaultParams);

        if (!empty($result['results'])) {
            return $this->movieBuilder->addRecommendationsFromTheMovieDB($movie, $result['results']);
        }

        return $movie;
    }

    protected function manyReviewsOfMovie(Movie\Movie $movie)
    {
        $id = $movie->identity();
        $result = $this->adapter->get("{$this->type}/$id/reviews", $this->defaultParams);

        if (!empty($result['results'])) {
            return $this->movieBuilder->addReviewsFromTheMovieDB($movie, $result['results']);
        }

        return $movie;
    }

    protected function manySimilarMoviesOfMovie(Movie\Movie $movie)
    {
        $id = $movie->identity();
        $result = $this->adapter->get("{$this->type}/$id/similar", $this->defaultParams);

        if (!empty($result['results'])) {
            return $this->movieBuilder->addSimilarMoviesFromTheMovieDB($movie, $result['results']);
        }

        return $movie;
    }

    public function manyWithTitle($title)
    {
        $movies = [];

        $result = $this->adapter->get("search/{$this->type}", array_merge($this->defaultParams, ['query' => $title]));

        if (!empty($result['results'])) {
            $title = strtolower(
                preg_replace('/\-(\-)+/', '-', preg_replace('/[^A-Za-z0-9]/', '-', $title))
            );

            foreach ($result['results'] as $item) {
                $matchedTitle = strtolower(
                    preg_replace('/\-(\-)+/', '-', preg_replace('/[^A-Za-z0-9]/', '-', $item['original_title']))
                );

                if ($matchedTitle == $title) {
                    $movie = $this->movieBuilder->buildFromTheMovieDB($item, $this->type);
                    $movies[] = $this->addAdditionalMetadata($movie);
                }
            }
        }

        return $movies;
    }

    public function manyWithTitleLike($title)
    {
        $movies = [];

        $result = $this->adapter->get("search/{$this->type}", array_merge($this->defaultParams, ['query' => $title]));

        if (!empty($result['results'])) {
            foreach ($result['results'] as $item) {
                $movie = $this->movieBuilder->buildFromTheMovieDB($item, $this->type);
                $movies[] = $this->addAdditionalMetadata($movie);
            }
        }

        return $movies;
    }

    public function oneOfId($id)
    {
        $result = $this->adapter->get("{$this->type}/$id", $this->defaultParams);

        if (!empty($result['status_code']) && $result['status_code'] == 34) {
            throw new Movie\NotFoundException('No movie was found.');
        }

        return $this->addAdditionalMetadata(
            $this->movieBuilder->buildFromTheMovieDB($result, $this->type)
        );
    }

    public function oneOfTitle($title, $year = null)
    {
        $this->skipAllMetadata = true;
        $movies = $this->manyWithTitle($title);
        $this->skipAllMetadata = false;

        foreach ($movies as $movie) {
            if (!empty($year)) {
                $interest = $movie->provideMovieInterest();

                if ($year == $interest['year']) {
                    return $this->addAdditionalMetadata($movie);
                }
            } else {
                return $this->addAdditionalMetadata($movie);
            }
        }

        if ($this->tryFuzzyOnFail) {
            $this->skipAllMetadata = true;
            $movies = $this->manyWithTitleLike($title);
            $this->skipAllMetadata = false;

            $result = $this->titleSimilarityScoringService->findClosestMatch($title, $movies);

            if ($result['score'] <= $this->threshold) {
                if (!empty($year)) {
                    $interest = $result['movie']->provideMovieInterest();

                    if ($interest['year'] == $year) {
                        return $this->addAdditionalMetadata($result['movie']);
                    }
                } else {
                    return $this->addAdditionalMetadata($result['movie']);
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
        $this->type = 'tv';
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
        $this->withAlternateTitles = true;
        return $this;
    }

    public function withAllData()
    {
        $this->withAlternateTitles = true;
        $this->withCast = true;
        $this->withKeywords = true;
        $this->withRecommendations = true;
        $this->withReviews = true;
        $this->withSimilarMovies = true;
        return $this;
    }

    public function withCast()
    {
        $this->withCast = true;
        return $this;
    }

    public function withKeywords()
    {
        $this->withKeywords = true;
        return $this;
    }

    public function withNewEpisodes()
    {
        return $this;
    }

    public function withNewMovies()
    {
        return $this;
    }

    public function withRecommendations()
    {
        $this->withRecommendations = true;
        return $this;
    }

    public function withReviews()
    {
        $this->withReviews = true;
        return $this;
    }

    public function withSimilarMovies()
    {
        $this->withSimilarMovies = true;
        return $this;
    }

    public function withUpdatedEpisodes()
    {
        return $this;
    }

    public function withUpdatedMovies()
    {
        return $this;
    }
}
