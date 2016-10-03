<?php

namespace Fusani\Movies\Domain\Model\Movie;

class Movie
{
    protected $id;
    protected $alternateTitles;
    protected $cast;
    protected $directors;
    protected $episodes;
    protected $plot;
    protected $poster;
    protected $rating;
    protected $sources;
    protected $title;
    protected $type;
    protected $year;

    public function __construct($id, $title, $type, $year)
    {
        $this->id = $id;
        $this->alternateTitles = [];
        $this->cast = [];
        $this->directors = [];
        $this->episodes = [];
        $this->sources = [];
        $this->title = $title;
        $this->type = $type;
        $this->year = $year;
    }

    public function addAlternateTitle($alternateTitle)
    {
        $this->alternateTitles[] = $alternateTitle;
    }

    public function addEpisode(Episode $episode)
    {
        $this->episodes[] = $episode;
    }

    public function addCast($cast)
    {
        $this->cast[] = $cast;
    }

    public function addDirector($director)
    {
        $this->directors[] = $director;
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

        $episodes = [];

        foreach ($this->episodes as $episode) {
            $episodes[] = $episode->provideEpisodeInterest();
        }

        return [
            'id' => $this->id,
            'alternateTitles' => $this->alternateTitles,
            'cast' => $this->cast,
            'directors' => $this->directors,
            'episodes' => $episodes,
            'plot' => $this->plot,
            'poster' => $this->poster,
            'rating' => $this->rating,
            'sources' => $sources,
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

        $episodes = [];

        foreach ($this->episodes as $episode) {
            $episodes[] = $episode->provideEpisodeInterest();
        }

        return [
            'id' => $this->id,
            'alternateTitles' => $this->alternateTitles,
            'cast' => $this->cast,
            'directors' => $this->directors,
            'episodes' => $episodes,
            'plot' => $this->plot,
            'poster' => $this->poster,
            'rating' => $this->rating,
            'sources' => $sources,
            'title' => $this->title,
            'type' => $this->type,
            'year' => $this->year,
        ];
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

    public function title()
    {
        return $this->title;
    }
}
