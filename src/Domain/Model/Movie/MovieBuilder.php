<?php

namespace Fusani\Movies\Domain\Model\Movie;

use DateTime;

class MovieBuilder
{
    public function addAlternateTitlesFromTheMovieDB(Movie $movie, array $data)
    {
        foreach ($data as $title) {
            $movie->addAlternateTitle($title['title']);
        }

        return $movie;
    }

    public function addCastFromTheMovieDB(Movie $movie, array $data)
    {
        if (!empty($data['cast'])) {
            foreach ($data['cast'] as $details) {
                $movie->addCast($details['name'], $details['character']);
            }
        }

        if (!empty($data['crew'])) {
            foreach ($data['crew'] as $details) {
                $movie->addCrew($details['name'], $details['job'], $details['department']);
            }
        }

        return $movie;
    }

    public function addKeywordsFromTheMovieDB(Movie $movie, array $keywords)
    {
        foreach ($keywords as $keyword) {
            $movie->addKeyword($keyword['name']);
        }

        return $movie;
    }

    public function addRecommendationsFromTheMovieDB(Movie $movie, array $recommendations)
    {
        foreach ($recommendations as $recommendation) {
            $movie->addRecommendation($this->buildFromTheMovieDB($recommendation, 'movie'));
        }

        return $movie;
    }

    public function addReviewsFromTheMovieDB(Movie $movie, array $reviews)
    {
        foreach ($reviews as $review) {
            $movie->addReview(new Review($review['content'], $review['author'], $review['url']));
        }

        return $movie;
    }

    public function addSimilarMoviesFromTheMovieDB(Movie $movie, array $similarMovies)
    {
        foreach ($similarMovies as $similarMovie) {
            $movie->addSimilarMovie($this->buildFromTheMovieDB($similarMovie, 'movie'));
        }

        return $movie;
    }

    public function buildFromGuidebox(array $data)
    {
        $movie = false;

        // several ways this can be a movie
        if (!empty($data['isMovie'])) {
            $movie = true;
        } elseif (empty($data['tvrage']['tvrage_id'])) {
            $movie = true;
        }

        $year = null;

        if (!empty($data['release_year'])) {
            $year = $data['release_year'];
        } elseif (!empty($data['first_aired'])) {
            $year = new \DateTime($data['first_aired']);
            $year = $year->format('Y');
        } elseif (!empty($data['release_date'])) {
            $year = new \DateTime($data['release_date']);
            $year = $year->format('Y');
        }

        $movie = new Movie(
            $data['id'],
            $data['title'],
            $movie ? 'movie' : 'tvshow',
            $year
        );

        $movie->addExternalId(new ExternalId($data['id'], 'Guidebox'));

        if (!empty($data['alternate_titles'])) {
            foreach ($data['alternate_titles'] as $alternateTitle) {
                $movie->addAlternateTitle($alternateTitle);
            }
        }

        if (!empty($data['overview'])) {
            $movie->setPlot($data['overview']);
        }

        if (!empty($data['poster_120x171'])) {
            $movie->setPoster($data['poster_120x171']);
        } elseif (!empty($data['artwork_208x117'])) {
            $movie->setPoster($data['artwork_208x117']);
        }

        if (!empty($data['rating'])) {
            $movie->setRating($data['rating']);
        }

        $sources = [];

        if (!empty($data['free_web_sources'])) {
            $sources['free'] = $data['free_web_sources'];
        }

        if (!empty($data['tv_everywhere_web_sources'])) {
            $sources['tvEverywhere'] = $data['tv_everywhere_web_sources'];
        }

        if (!empty($data['subscription_web_sources'])) {
            $sources['subscription'] = $data['subscription_web_sources'];
        }

        if (!empty($data['purchase_web_sources'])) {
            $sources['purchase'] = $data['purchase_web_sources'];
        }

        foreach ($sources as $type => $sourceList) {
            foreach ($sourceList as $source) {
                $movie->addSource(
                    $type,
                    $source['display_name'],
                    $source['link'],
                    empty($source['formats']) ? [] : $source['formats']
                );
            }
        }

        if (!empty($data['cast'])) {
            foreach ($data['cast'] as $cast) {
                $movie->addCast($cast['name'], $cast['character_name']);
            }
        }

        if (!empty($data['directors'])) {
            foreach ($data['directors'] as $director) {
                $movie->addDirector($director['name']);
            }
        }

        return $movie;
    }

    public function buildFromNetflix(array $data)
    {
        $movie = new Movie(
            $data['show_id'],
            $data['show_title'],
            $data['mediatype'] == 0 ? 'movie' : 'tvshow',
            $data['release_year']
        );
        $movie->setPoster($data['poster']);
        $movie->setPlot($data['summary']);
        $movie->addExternalId(new ExternalId($data['show_id'], 'Netflix'));
        return $movie;
    }

    public function buildFromOmdb(array $data)
    {
        $movie = new Movie($data['imdbID'], $data['Title'], $data['Type'], $data['Year']);
        $movie->addExternalId(new ExternalId($data['imdbID'], 'OMDB'));

        if (!empty($data['Poster']) && $data['Poster'] != 'N/A') {
            $movie->setPoster($data['Poster']);
        }

        if (!empty($data['Plot'])) {
            $movie->setPlot($data['Plot']);
        }

        return $movie;
    }

    public function buildFromTheMovieDB(array $data, $type)
    {
        $releaseDate = new DateTime(empty($data['release_date']) ? $data['first_air_date'] : $data['release_date']);

        $movie = new Movie(
            $data['id'],
            empty($data['title']) ? $data['original_name'] : $data['title'],
            $type,
            $releaseDate->format('Y')
        );
        $movie->addExternalId(new ExternalId($data['id'], 'The Movie DB'));

        if (!empty($data['original_title'])) {
            $movie->addAlternateTitle($data['original_title']);
        }

        if (!empty($data['name']) && !empty($data['original_name']) && $data['original_name'] != $data['name']) {
            $movie->addAlternateTitle($data['name']);
        }

        $movie->setPlot($data['overview']);

        if (!empty($data['belongs_to_collection'])) {
            $movie->setCollection($data['belongs_to_collection']['name']);
        }

        if (!empty($data['budget'])) {
            $movie->setBudget($data['budget']);
        }

        if (!empty($data['genres']) && is_array($data['genres'])) {
            foreach ($data['genres'] as $genre) {
                $movie->addGenre($genre['name']);
            }
        }

        if (!empty($data['homepage'])) {
            $movie->setHomepage($data['homepage']);
        }

        if (!empty($data['imdb_id'])) {
            $movie->addExternalId(new ExternalId($data['imdb_id'], 'IMDB'));
        }

        if (!empty($data['original_language'])) {
            $movie->addLanguage($data['original_language']);
        }

        if (!empty($data['production_companies'])) {
            foreach ($data['production_companies'] as $productionCompany) {
                $movie->addProductionCompany($productionCompany['name']);
            }
        }

        if (!empty($data['revenue'])) {
            $movie->setRevenue($data['revenue']);
        }

        if (!empty($data['runtime'])) {
            $movie->setRuntime($data['runtime']);
        }

        if (!empty($data['status'])) {
            $movie->setStatus($data['status']);
        }

        if (!empty($data['tagline'])) {
            $movie->setTagline($data['tagline']);
        }

        return $movie;
    }
}
