<?php

namespace Fusani\Movies\Domain\Model\Movie;

class MovieBuilder
{
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
