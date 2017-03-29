<?php

namespace Fusani\Movies\Infrastructure\Persistence\Netflix;

use Fusani\Movies\Domain\Model\Movie;
use Fusani\Movies\Infrastructure\Adapter;

/**
 * This is not the official Netflix API but uses the Netflix Roulette open API. If using this repository, please
 * credit the Netflix Roulette API.
 *
 * This API only supports online streaming movies.
 */
class MovieRepository implements Movie\MovieRepository
{
    protected $adapter;
    protected $movieBuilder;

    public function __construct(Adapter\Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->movieBuilder = new Movie\MovieBuilder();
    }

    public function doNotTryFuzzyOnFail()
    {
        // this api does not support fuzzy matching
        throw new NotYetImplementedException();
    }

    public function many($startAt, $numRecords, $type)
    {
        // this api does not support grabbing all records
        throw new NotYetImplementedException();
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

    public function manyMoviesWithChanges($time)
    {
        throw new NotYetImplementedException();
    }

    public function manyWithTitle($title)
    {
        // unfortunately, this api does not support this kind of method
        throw new NotYetImplementedException();
    }

    public function manyWithTitleLike($title)
    {
        // unfortunately, this api does not support this kind of method
        throw new NotYetImplementedException();
    }

    public function oneOfId($id)
    {
        // unfortunately, this api does not support this kind of method
        throw new NotYetImplementedException();
    }

    public function oneOfTitle($title, $year = null)
    {
        $params = ['title' => $title];

        if ($year) {
            $params['year'] = $year;
        }

        $result = $this->adapter->get('', $params);

        if ((!empty($result['errorcode']) && $result['errorcode'] == 404) || !$result) {
            throw new Movie\NotFoundException('No movie was found.');
        }

        return $this->movieBuilder->buildFromNetflix($result);
    }

    public function searchForMovies()
    {
        // this function does nothing but is here to fit the interface definition
        return $this;
    }

    public function searchForShows()
    {
        throw new NotYetImplementedException();
    }

    public function setThreshold($threshold)
    {
        // this api does not support fuzzy matching
        throw new NotYetImplementedException();
    }

    public function tryFuzzyOnFail()
    {
        // this api does not support fuzzy matching
        throw new NotYetImplementedException();
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
        return $this;
    }

    public function withNewMovies()
    {
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
        return $this;
    }

    public function withUpdatedMovies()
    {
        return $this;
    }
}
