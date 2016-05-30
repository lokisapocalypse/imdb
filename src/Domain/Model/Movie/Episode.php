<?php

namespace Fusani\Movies\Domain\Model\Movie;

class Episode
{
    protected $id;
    protected $episode;
    protected $firstAired;
    protected $plot;
    protected $poster;
    protected $season;
    protected $sources;
    protected $title;

    public function __construct($id, $title, $firstAired, $season, $episode)
    {
        $this->episode = $episode;
        $this->firstAired = $firstAired;
        $this->id = $id;
        $this->season = $season;
        $this->sources = [];
        $this->title = $title;
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

    public function provideEpisodeInterest()
    {
        $sources = [];

        foreach ($this->sources as $type => $sourceList) {
            foreach ($sourceList as $source) {
                $sources[$type][] = $source->provideSourceInterest();
            }
        }

        return [
            'id' => $this->id,
            'episode' => $this->episode,
            'firstAired' => $this->firstAired,
            'plot' => $this->plot,
            'poster' => $this->poster,
            'season' => $this->season,
            'sources' => $sources,
            'title' => $this->title,
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
