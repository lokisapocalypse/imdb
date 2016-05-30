<?php

namespace Fusani\Movies\Domain\Model\Movie;

interface MovieRepository
{
    public function manyEpisodesOfShow(
        Movie $movie,
        $id,
        $includeLinks = false,
        $reverseOrder = false,
        $season = 'all',
        $startAt = 0,
        $limit = 25,
        $sources = 'all',
        $platform = 'all'
    );
    public function manyWithTitle($title);
    public function manyWithTitleLike($title);
    public function oneOfId($id);
    public function oneOfTitle($title, $year = null);
    public function searchForMovies();
    public function searchForShows();
}
