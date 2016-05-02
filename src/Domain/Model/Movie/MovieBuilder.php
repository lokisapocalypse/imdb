<?php

namespace Fusani\Movies\Domain\Model\Movie;

class MovieBuilder
{
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
        return $movie;
    }

    public function buildFromOmdb(array $data)
    {
        $movie = new Movie($data['imdbID'], $data['Title'], $data['Type'], $data['Year']);

        if ($data['Poster'] != 'N/A') {
            $movie->setPoster($data['Poster']);
        }

        if (!empty($data['Plot'])) {
            $movie->setPlot($data['Plot']);
        }

        return $movie;
    }
}
