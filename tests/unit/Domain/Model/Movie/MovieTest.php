<?php

namespace Fusani\Omdb\Domain\Model\Movie;

use PHPUnit_Framework_TestCase;

class MovieTest extends PHPUnit_Framework_TestCase
{
    public function testProvideInterestSimpleObject()
    {
        $movie = new Movie(15);
        $interest = $movie->provideMovieInterest();

        $this->assertEquals(15, $interest['id']);

        unset($interest['id']);

        // everything else should be null
        foreach ($interest as $field => $value) {
            $this->assertNull($value);
        }
    }

    public function testPopulate()
    {
        $movie = new Movie(15);
        $movie->populate(
            'Pose 1',
            'Guardians of the Galaxy',
            'Movie',
            2012
        );

        $expected = [
            'id' => 15,
            'poster' => 'Pose 1',
            'title' => 'Guardians of the Galaxy',
            'type' => 'Movie',
            'year' => 2012,
        ];

        $this->assertEquals($expected, $movie->provideMovieInterest());
    }
}
