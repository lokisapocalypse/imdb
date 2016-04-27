<?php

namespace Fusani\Movies\Domain\Model\Movie;

class Movie
{
    protected $id;
    protected $plot;
    protected $poster;
    protected $title;
    protected $type;
    protected $year;

    public function __construct($id, $title, $type, $year)
    {
        $this->id = $id;
        $this->title = $title;
        $this->type = $type;
        $this->year = $year;
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
            'title' => $this->title,
            'type' => $this->type,
            'year' => $this->year,
        ];
    }
}
