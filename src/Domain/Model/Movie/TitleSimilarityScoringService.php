<?php

namespace Fusani\Movies\Domain\Model\Movie;

use InvalidArgumentException;

class TitleSimilarityScoringService
{
    public function findClosestMatch($title, array $movies)
    {
        $minScore = 999999;
        $matchIndex = 0;
        $title = strtolower($title);

        if (empty($movies)) {
            throw new InvalidArgumentException('No movies were provided');
        }

        foreach ($movies as $index => $movie) {
            $interest = $movie->provideMovieInterest();
            $titles = array_merge([$interest['title']], $interest['alternateTitles']);

            foreach ($titles as $alternateTitle) {
                $alternateTitle = strtolower($alternateTitle);
                $score = levenshtein($title, $alternateTitle);

                if ($score < $minScore) {
                    $minScore = $score;
                    $matchIndex = $index;
                }
            }
        }

        return [
            'movie' => $movies[$matchIndex],
            'score' => $minScore,
        ];
    }
}
