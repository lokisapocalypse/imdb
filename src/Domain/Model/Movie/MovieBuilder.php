<?php

namespace Fusani\Movies\Domain\Model\Movie;

class MovieBuilder
{
    public function buildFromGuidebox(array $data)
    {
        $movie = false;

        // several ways this can be a movie
        if (!empty($data['isMovie'])) {
            $movie = true;
        } elseif (empty($data['tvrage']['tvrage_id'])) {
            $movie = true;
        }

        $movie = new Movie(
            $data['id'],
            $data['title'],
            $movie ? 'movie' : 'tvshow',
            $data['release_year']
        );

        if (!empty($data['overview'])) {
            $movie->setPlot($data['overview']);
        }

        $movie->setPoster($data['poster_120x171']);

        $sources = [];

        if (!empty($data['free_web_sources'])) {
            $sources['free'] = $data['free_web_sources'];
        }

        if (!empty($data['tv_everywhere_web_sources'])) {
            $sources['tvEverywhere'] = $data['tv_everywhere_web_sources'];
        }

        if (!empty($data['subscription_web_sources'])) {
            $sources['subscription'] = $data['subscription_web_sources'];
        }

        if (!empty($data['purchase_web_sources'])) {
            $sources['purchase'] = $data['purchase_web_sources'];
        }

        foreach ($sources as $type => $sourceList) {
            foreach ($sourceList as $source) {
                $movie->addSource(
                    $type,
                    $source['display_name'],
                    $source['link'],
                    empty($source['formats']) ? [] : $source['formats']
                );
            }
        }

        return $movie;
    }

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
