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

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function populate($plot, $poster, $title, $type, $year)
    {
        $this->plot = $plot;
        $this->poster = $poster;
        $this->title = $title;
        $this->type = $type;
        $this->year = $year;
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
