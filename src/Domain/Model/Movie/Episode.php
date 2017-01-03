<?php

namespace Fusani\Movies\Domain\Model\Movie;

class Episode
{
    protected $id;
    protected $cast;
    protected $crew;
    protected $episode;
    protected $firstAired;
    protected $plot;
    protected $posters;
    protected $season;
    protected $sources;
    protected $title;

    public function __construct($id, $title, $firstAired, $season, $episode)
    {
        $this->cast = [];
        $this->crew = [];
        $this->episode = $episode;
        $this->firstAired = $firstAired;
        $this->id = $id;
        $this->posters = [];
        $this->season = $season;
        $this->sources = [];
        $this->title = $title;
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

    public function addPoster($link, $type, $width = 0, $height = 0)
    {
        $poster = new Poster($link, $type, $width, $height);

        foreach ($this->posters as $existingPoster) {
            if ($existingPoster->identity() == $poster->identity()) {
                return $this;
            }
        }

        $this->posters[] = $poster;
        return $this;
    }

    public function addSource($type, $name, $link, array $details = [])
    {
        if (!empty($this->sources[$type])) {
            foreach ($this->sources[$type] as $source) {
                $interest = $source->provideSourceInterest();

                if ($interest['name'] == $name && $interest['link'] == $link) {
                    return $this;
                }
            }
        }

        $source = new Source($type, $name, $link, $details);
        $this->sources[$type][] = $source;
        return $this;
    }

    public function identity()
    {
        return sprintf('s%02de%02d-%d', $this->season, $this->episode, $this->id);
    }

    public function provideEpisodeInterest()
    {
        $sources = [];

        foreach ($this->sources as $type => $sourceList) {
            foreach ($sourceList as $source) {
                $sources[$type][] = $source->provideSourceInterest();
            }
        }

        $cast = array_map(function ($c) {
            return $c->provideCastInterest();
        }, $this->cast);

        $crew = array_map(function ($c) {
            return $c->provideCrewInterest();
        }, $this->crew);

        $posters = array_map(function ($p) {
            return $p->providePosterInterest();
        }, $this->posters);

        return [
            'id' => $this->id,
            'cast' => $cast,
            'crew' => $crew,
            'episode' => $this->episode,
            'firstAired' => $this->firstAired,
            'plot' => $this->plot,
            'posters' => $posters,
            'season' => $this->season,
            'sources' => $sources,
            'title' => $this->title,
        ];
    }

    public function setPlot($plot)
    {
        $this->plot = $plot;
    }

    public function title()
    {
        return $this->title;
    }
}
