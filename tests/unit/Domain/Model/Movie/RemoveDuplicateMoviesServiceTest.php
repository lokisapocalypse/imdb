<?php

namespace Fusani\Movies\Domain\Model\Movie;

use PHPUnit_Framework_TestCase;

class RemoveDuplicateMoviesServiceTest extends PHPUnit_Framework_TestCase
{
    protected $service;

    public function setup()
    {
        $this->service = new RemoveDuplicateMoviesService();
    }

    public function testRemoveDuplicatesEmptyArrayStaysEmpty()
    {
        $uniqueMovies = $this->service->removeDuplicates([]);
        $this->assertEquals([], $uniqueMovies);
    }

    public function testRemoveDuplicatesWithUniqueArrayStaysTheSame()
    {
        $movies = [
            new Movie(1, 'Guardians of the Galaxy', 'movie', 2014),
            new Movie(2, 'Ghostbusters', 'movie', 1984),
            new Movie(3, 'Road House', 'movie', 1989),
        ];

        $uniqueMovies = $this->service->removeDuplicates($movies);

        $this->assertEquals($uniqueMovies, $movies);
    }

    public function testRemoveDuplicatesRemovesDuplicates()
    {
        $movies = [
            new Movie(1, 'Guardians of the Galaxy', 'movie', 2014),
            new Movie(2, 'Ghostbusters', 'movie', 1984),
            new Movie(3, 'Road House', 'movie', 1989),
            new Movie(4, 'Guardians of the Galaxy', 'movie', 2014),
            new Movie(5, 'Road House', 'movie', 1989),
        ];

        $uniqueMovies = $this->service->removeDuplicates($movies);
        $expected = [
            $movies[3], $movies[1], $movies[4],
        ];

        $this->assertEquals($expected, $uniqueMovies);
    }
}
