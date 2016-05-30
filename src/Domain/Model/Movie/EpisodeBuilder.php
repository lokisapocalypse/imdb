<?php

namespace Fusani\Movies\Domain\Model\Movie;

use DateTime;

class EpisodeBuilder
{
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
}
