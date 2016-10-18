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

        $year = null;

        if (!empty($data['release_year'])) {
            $year = $data['release_year'];
        } elseif (!empty($data['first_aired'])) {
            $year = new \DateTime($data['first_aired']);
            $year = $year->format('Y');
        } elseif (!empty($data['release_date'])) {
            $year = new \DateTime($data['release_date']);
            $year = $year->format('Y');
        }

        $movie = new Movie(
            $data['id'],
            $data['title'],
            $movie ? 'movie' : 'tvshow',
            $year
        );

        if (!empty($data['alternate_titles'])) {
            foreach ($data['alternate_titles'] as $alternateTitle) {
                $movie->addAlternateTitle($alternateTitle);
            }
        }

        if (!empty($data['overview'])) {
            $movie->setPlot($data['overview']);
        }

        if (!empty($data['poster_120x171'])) {
            $movie->setPoster($data['poster_120x171']);
        } elseif (!empty($data['artwork_208x117'])) {
            $movie->setPoster($data['artwork_208x117']);
        }

        if (!empty($data['rating'])) {
            $movie->setRating($data['rating']);
        }

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

        if (!empty($data['cast'])) {
            foreach ($data['cast'] as $cast) {
                $movie->addCast($cast['name']);
            }
        }

        if (!empty($data['directors'])) {
            foreach ($data['directors'] as $director) {
                $movie->addDirector($director['name']);
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

        if (!empty($data['Poster']) && $data['Poster'] != 'N/A') {
            $movie->setPoster($data['Poster']);
        }

        if (!empty($data['Plot'])) {
            $movie->setPlot($data['Plot']);
        }

        return $movie;
    }
}
