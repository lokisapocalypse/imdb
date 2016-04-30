<?php

namespace Fusani\Movies\Domain\Model\Movie;

class RemoveDuplicateMoviesService
{
    /**
     * This function is necessary because the omdb api has entries with
     * duplicate information but different imdb id's. I did not want to impose
     * removing the duplicates on the repository so this is a domain service
     * added for users to use or they can implement their own.
     *
     * @param array $movies : the movies to remove duplicates from
     * @return array of unique movies
     */
    public function removeDuplicates(array $movies)
    {
        $uniqueMovies = [];

        foreach ($movies as $movie) {
            $interest = $movie->provideMovieInterest();

            // here, uniqueness is defined by having the same title and year
            // collisions simply overwrite the previous value but that shouldn't matter
            $uniqueMovies[$interest['title'].$interest['year']] = $movie;
        }

        return array_values($uniqueMovies);
    }
}
