<?php

namespace Fusani\Movies\Domain\Model\Movie;

class Movie
{
    protected $id;
    protected $plot;
    protected $poster;
    protected $sources;
    protected $title;
    protected $type;
    protected $year;

    public function __construct($id, $title, $type, $year)
    {
        $this->id = $id;
        $this->sources = [];
        $this->title = $title;
        $this->type = $type;
        $this->year = $year;
    }

    public function isTheSameAs(Movie $movie)
    {
        return $this->title == $movie->title
            && $this->year == $movie->year;
    }

    public function setPlot($plot)
    {
        $this->plot = $plot;
    }

    public function setPoster($poster)
    {
        $this->poster = $poster;
    }

    public function provideMovieInterest()
    {
        return [
            'id' => $this->id,
            'plot' => $this->plot,
            'poster' => $this->poster,
            'sources' => $this->sources,
            'title' => $this->title,
            'type' => $this->type,
            'year' => $this->year,
        ];
    }

    public function updateWith($source, Movie $movie)
    {
        $this->sources[strtolower($source)] = $movie->provideMovieInterest();
    }
}
