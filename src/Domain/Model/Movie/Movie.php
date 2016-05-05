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

    public function addSource($type, $name, $link, array $details = [])
    {
        $source = new Source($type, $name, $link, $details);
        $this->sources[$type][] = $source;
    }

    public function identity()
    {
        return $this->id;
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
        }

        return [
            'id' => $this->id,
            'plot' => $this->plot,
            'poster' => $this->poster,
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

    public function title()
    {
        return $this->title;
    }
}
