<?php

namespace Fusani\Movies\Domain\Model\Movie;

class Movie
{
    protected $id;
    protected $alternateTitles;
    protected $budget;
    protected $cast;
    protected $collection;
    protected $crew;
    protected $directors;
    protected $episodes;
    protected $externalIds;
    protected $genres;
    protected $homepage;
    protected $keywords;
    protected $languages;
    protected $plot;
    protected $poster;
    protected $productionCompanies;
    protected $productionCountries;
    protected $rating;
    protected $recommendations;
    protected $revenue;
    protected $reviews;
    protected $runtime;
    protected $similarMovies;
    protected $sources;
    protected $status;
    protected $tagline;
    protected $title;
    protected $type;
    protected $year;

    public function __construct($id, $title, $type, $year)
    {
        $this->id = $id;
        $this->alternateTitles = [];
        $this->cast = [];
        $this->crew = [];
        $this->directors = [];
        $this->episodes = [];
        $this->externalIds = [];
        $this->genres = [];
        $this->keywords = [];
        $this->languages = [];
        $this->productionCompanies = [];
        $this->productionCountries = [];
        $this->recommendations = [];
        $this->reviews = [];
        $this->similarMovies = [];
        $this->sources = [];
        $this->title = $title;
        $this->type = $type;
        $this->year = $year;
    }

    public function addAlternateTitle($alternateTitle)
    {
        if (!in_array($alternateTitle, $this->alternateTitles)) {
            $this->alternateTitles[] = $alternateTitle;
        }
    }

    public function addCast($name, $character)
    {
        foreach ($this->cast as $cast) {
            $interest = $cast->provideCastInterest();

            if ($interest['actor'] == $name && $interest['character'] == $character) {
                return $this;
            }
        }

        $this->cast[] = new Cast($name, $character);
        return $this;
    }

    public function addCrew($name, $job, $department)
    {
        $newCrew = [
            'department' => $department,
            'job' => $job,
            'name' => $name,
        ];

        foreach ($this->crew as $crew) {
            if ($newCrew == $crew->provideCrewInterest()) {
                return $this;
            }
        }

        $this->crew[] = new Crew($name, $job, $department);
        return $this;
    }

    public function addDirector($director)
    {
        if (!in_array($director, $this->directors)) {
            $this->directors[] = $director;
        }

        return $this;
    }

    public function addEpisode(Episode $episode)
    {
        foreach ($this->episodes as $ep) {
            if ($ep->identity() == $episode->identity()) {
                return $this;
            }
        }

        $this->episodes[] = $episode;
        return $this;
    }

    public function addExternalId($id, $source)
    {
        foreach ($this->externalIds as $externalId) {
            $interest = $externalId->provideExternalIdInterest();

            if ($interest['externalId'] == $id && $interest['source'] == $source) {
                return $this;
            }
        }

        $externalId = new ExternalId($id, $source);
        $this->externalIds[] = $externalId;

        return $this;
    }

    public function addGenre($genre)
    {
        if (!in_array($genre, $this->genres)) {
            $this->genres[] = $genre;
        }

        return $this;
    }

    public function addKeyword($keyword)
    {
        if (!in_array($keyword, $this->keywords)) {
            $this->keywords[] = $keyword;
        }

        return $this;
    }

    public function addLanguage($language)
    {
        if (!in_array($language, $this->languages)) {
            $this->languages[] = $language;
        }

        return $this;
    }

    public function addProductionCompany($productionCompany)
    {
        if (!in_array($productionCompany, $this->productionCompanies)) {
            $this->productionCompanies[] = $productionCompany;
        }

        return $this;
    }

    public function addProductionCountry($productionCountry)
    {
        if (!in_array($productionCountry, $this->productionCountries)) {
            $this->productionCountries[] = $productionCountry;
        }

        return $this;
    }

    public function addRecommendation(Movie $movie)
    {
        foreach ($this->recommendations as $recommendation) {
            if ($movie->identity() == $recommendation->identity()) {
                return $this;
            }
        }

        $this->recommendations[] = $movie;
        return $this;
    }

    public function addReview($review, $author, $link)
    {
        foreach ($this->reviews as $existingReview) {
            $interest = $existingReview->provideReviewInterest();

            if ($interest['review'] == $review
                && $interest['author'] == $author
                && $interest['link'] == $link
            ) {
                return $this;
            }
        }

        $review = new Review($review, $author, $link);
        $this->reviews[] = $review;
        return $this;
    }

    public function addSimilarMovie(Movie $similarMovie)
    {
        $interest = $similarMovie->provideMovieInterest();

        foreach ($this->similarMovies as $existingSimilarMovie) {
            $existingSimilarMovieInterest = $existingSimilarMovie->provideMovieInterest();

            if ($existingSimilarMovieInterest == $interest) {
                return $this;
            }
        }

        $this->similarMovies[] = $similarMovie;
        return $this;
    }

    public function addSource($type, $name, $link, array $details = [])
    {
        $source = new Source($type, $name, $link, $details);
        $this->sources[$type][] = $source;
    }

    public function identity()
    {
        return $this->id;
    }

    public function hasSource($name, $type)
    {
        if (empty($this->sources[$type])) {
            return false;
        }

        foreach ($this->sources[$type] as $source) {
            $interest = $source->provideSourceInterest();

            if ($interest['name'] == $name) {
                return true;
            }
        }

        return false;
    }

    public function isTheSameAs(Movie $movie)
    {
        return $this->title == $movie->title
            && $this->year == $movie->year;
    }

    public function provideMovieInterest()
    {
        $sources = [];

        foreach ($this->sources as $type => $sourceList) {
            foreach ($sourceList as $source) {
                $sources[$type][] = $source->provideSourceInterest();
            }

            usort($sources[$type], function ($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });
        }

        $cast = array_map(function ($c) {
            return $c->provideCastInterest();
        }, $this->cast);

        $crew = array_map(function ($c) {
            return $c->provideCrewInterest();
        }, $this->crew);

        $episodes = array_map(function ($e) {
            return $e->provideEpisodeInterest();
        }, $this->episodes);

        $externalIds = array_map(function ($e) {
            return $e->provideExternalIdInterest();
        }, $this->externalIds);

        $recommendations = array_map(function ($r) {
            return $r->provideMovieInterest();
        }, $this->recommendations);

        $reviews = array_map(function ($r) {
            return $r->provideReviewInterest();
        }, $this->reviews);

        $similarMovies = array_map(function ($s) {
            return $s->provideMovieInterest();
        }, $this->similarMovies);

        return [
            'id' => $this->id,
            'alternateTitles' => $this->alternateTitles,
            'budget' => $this->budget,
            'cast' => $cast,
            'collection' => $this->collection,
            'crew' => $crew,
            'directors' => $this->directors,
            'episodes' => $episodes,
            'externalIds' => $externalIds,
            'genres' => $this->genres,
            'homepage' => $this->homepage,
            'keywords' => $this->keywords,
            'languages' => $this->languages,
            'plot' => $this->plot,
            'poster' => $this->poster,
            'productionCompanies' => $this->productionCompanies,
            'productionCountries' => $this->productionCountries,
            'rating' => $this->rating,
            'recommendations' => $recommendations,
            'revenue' => $this->revenue,
            'reviews' => $reviews,
            'runtime' => $this->runtime,
            'similarMovies' => $similarMovies,
            'sources' => $sources,
            'status' => $this->status,
            'tagline' => $this->tagline,
            'title' => $this->title,
            'type' => $this->type,
            'year' => $this->year,
        ];
    }

    public function provideMovieWithSourcesConsolidatedInterest()
    {
        $sources = [];

        foreach ($this->sources as $sourceList) {
            foreach ($sourceList as $source) {
                $sources[] = $source->provideSourceInterest();
            }
        }

        /**
         * @codeCoverageIgnore
         */
        usort($sources, function ($a, $b) {
            if ($a['type'] == $b['type']) {
                return strcasecmp($a['name'], $b['name']);
            }

            if ($a['type'] == 'free') {
                return -1;
            } elseif ($b['type'] == 'free') {
                return 1;
            }

            if ($a['type'] == 'tvEverywhere') {
                return -1;
            } elseif ($b['type'] == 'tvEverywhere') {
                return 1;
            }

            if ($a['type'] == 'subscription') {
                return -1;
            } elseif ($b['type'] == 'subscription') {
                return 1;
            }

            return 0;
        });

        $cast = array_map(function ($c) {
            return $c->provideCastInterest();
        }, $this->cast);

        $crew = array_map(function ($c) {
            return $c->provideCrewInterest();
        }, $this->crew);

        $episodes = array_map(function ($e) {
            return $e->provideEpisodeInterest();
        }, $this->episodes);

        $externalIds = array_map(function ($e) {
            return $e->provideExternalIdInterest();
        }, $this->externalIds);

        $recommendations = array_map(function ($r) {
            return $r->provideMovieInterest();
        }, $this->recommendations);

        $reviews = array_map(function ($r) {
            return $r->provideReviewInterest();
        }, $this->reviews);

        $similarMovies = array_map(function ($s) {
            return $s->provideMovieInterest();
        }, $this->similarMovies);

        return [
            'id' => $this->id,
            'alternateTitles' => $this->alternateTitles,
            'budget' => $this->budget,
            'cast' => $cast,
            'collection' => $this->collection,
            'crew' => $crew,
            'directors' => $this->directors,
            'episodes' => $episodes,
            'externalIds' => $externalIds,
            'genres' => $this->genres,
            'homepage' => $this->homepage,
            'keywords' => $this->keywords,
            'languages' => $this->languages,
            'plot' => $this->plot,
            'poster' => $this->poster,
            'productionCompanies' => $this->productionCompanies,
            'productionCountries' => $this->productionCountries,
            'rating' => $this->rating,
            'recommendations' => $recommendations,
            'revenue' => $this->revenue,
            'reviews' => $reviews,
            'runtime' => $this->runtime,
            'similarMovies' => $similarMovies,
            'sources' => $sources,
            'status' => $this->status,
            'tagline' => $this->tagline,
            'title' => $this->title,
            'type' => $this->type,
            'year' => $this->year,
        ];
    }

    public function setBudget($budget)
    {
        $this->budget = $budget;
    }

    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;
    }

    public function setPlot($plot)
    {
        $this->plot = $plot;
    }

    public function setPoster($poster)
    {
        $this->poster = $poster;
    }

    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    public function setRevenue($revenue)
    {
        $this->revenue = $revenue;
    }

    public function setRuntime($runtime)
    {
        $this->runtime = $runtime;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setTagline($tagline)
    {
        $this->tagline = $tagline;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setYear($year)
    {
        $this->year = $year;
    }

    public function title()
    {
        return $this->title;
    }
}
