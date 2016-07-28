<?php

namespace Fusani\Movies\Domain\Model\Movie;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

class TitleSimilarityScoringServiceTest extends PHPUnit_Framework_TestCase
{
    protected $service;

    public function setup()
    {
        $this->service = new TitleSimilarityScoringService();
    }

    public function testFindClosestMatchWithNoMovies()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $result = $this->service->findClosestMatch('Guardians of the Galaxy', []);
    }

    public function testFindClosestMatchWithExactMatchInTitle()
    {
        $movies = [
            new Movie(1, 'Road House', 'movie', 1989),
            new Movie(2, 'Ghostbusters', 'movie', 1984),
            new Movie(3, 'Guardians of the Galaxy', 'movie', 2014),
        ];

        $result = $this->service->findClosestMatch('Guardians of the Galaxy', $movies);
        $this->assertEquals(0, $result['score']);
        $this->assertEquals($movies[2], $result['movie']);
    }

    public function testFindClosestMatchWithCloseTitle()
    {
        $movies = [
            new Movie(1, 'Road House', 'movie', 1989),
            new Movie(2, 'Ghostbusters', 'movie', 1984),
            new Movie(3, 'Guardians of the Galaxy II', 'movie', 2014),
        ];

        $result = $this->service->findClosestMatch('Guardians of the Galaxy', $movies);
        $this->assertEquals(3, $result['score']);
        $this->assertEquals($movies[2], $result['movie']);
    }
}
