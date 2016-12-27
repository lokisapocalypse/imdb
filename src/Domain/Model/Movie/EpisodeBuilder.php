<?php

namespace Fusani\Movies\Domain\Model\Movie;

use DateTime;

class EpisodeBuilder
{
    public function buildFromEpisodeInterest(array $interest)
    {
        $episode = new Episode(
            $interest['id'],
            $interest['title'],
            $interest['firstAired'],
            $interest['season'],
            $interest['episode']
        );

        $episode->setPlot($interest['plot']);
        $episode->setPoster($interest['poster']);

        foreach ($interest['cast'] as $cast) {
            $episode->addCast($cast['actor'], $cast['character']);
        }

        foreach ($interest['crew'] as $crew) {
            $episode->addCrew($crew['name'], $crew['job'], $crew['department']);
        }

        foreach ($interest['sources'] as $type => $sources) {
            foreach ($sources as $source) {
                $episode->addSource(
                    $type,
                    $source['name'],
                    $source['link'],
                    $source['details']
                );
            }
        }

        return $episode;
    }

    public function buildFromGuideBox(array $details)
    {
        $episode = new Episode(
            $details['id'],
            $details['title'],
            new DateTime($details['first_aired']),
            $details['season_number'],
            $details['episode_number']
        );
        $episode->setPlot($details['overview']);
        $episode->setPoster($details['thumbnail_208x117']);

        $sources = [];

        if (!empty($details['free_web_sources'])) {
            $sources['free'] = $details['free_web_sources'];
        }

        if (!empty($details['tv_everywhere_web_sources'])) {
            $sources['tvEverywhere'] = $details['tv_everywhere_web_sources'];
        }

        if (!empty($details['subscription_web_sources'])) {
            $sources['subscription'] = $details['subscription_web_sources'];
        }

        if (!empty($details['purchase_web_sources'])) {
            $sources['purchase'] = $details['purchase_web_sources'];
        }

        foreach ($sources as $type => $sourceList) {
            foreach ($sourceList as $source) {
                $episode->addSource(
                    $type,
                    $source['display_name'],
                    $source['link'],
                    empty($source['formats']) ? [] : $source['formats']
                );
            }
        }

        return $episode;
    }

    public function buildFromTheMovieDB(array $data)
    {
        $episode = new Episode(
            $data['id'],
            $data['name'],
            $data['air_date'],
            $data['season_number'],
            $data['episode_number']
        );

        if (!empty($data['crew'])) {
            foreach ($data['crew'] as $crew) {
                $episode->addCrew($crew['name'], $crew['job'], $crew['department']);
            }
        }

        if (!empty($data['guest_stars'])) {
            foreach ($data['guest_stars'] as $guestStar) {
                $episode->addCast($guestStar['name'], $guestStar['character']);
            }
        }

        $episode->setPlot($data['overview']);

        return $episode;
    }
}
