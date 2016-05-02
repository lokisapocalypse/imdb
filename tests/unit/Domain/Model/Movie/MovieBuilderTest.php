<?php

namespace Fusani\Movies\Domain\Model\Movie;

use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Domain\Model\Movie\MovieBuilder
 */
class MovieBuilderTest extends PHPUnit_Framework_TestCase
{
    protected $builder;

    public function setup()
    {
        $this->builder = new MovieBuilder();
    }

    public function testBuildWithOmdbNoPoster()
    {
        $data = [
            'Title' => 'Guardians of the Galaxy',
            'Plot' => 'Superheros save the world',
            'Poster' => 'N/A',
            'Type' => 'movie',
            'Year' => 2014,
            'imdbID' => 15,
        ];

        $expected = [
            'id' => 15,
            'plot' => 'Superheros save the world',
            'poster' => null,
            'title' => 'Guardians of the Galaxy',
            'type' => 'movie',
            'year' => 2014,
        ];

        $movie = $this->builder->buildFromOmdb($data);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testBuildWithOmdbNoPlot()
    {
        $data = [
            'Title' => 'Guardians of the Galaxy',
            'Poster' => 'www.movieposters.com/guardians-of-the-galaxy',
            'Type' => 'movie',
            'Year' => 2014,
            'imdbID' => 15,
        ];

        $expected = [
            'id' => 15,
            'plot' => null,
            'poster' => 'www.movieposters.com/guardians-of-the-galaxy',
            'title' => 'Guardians of the Galaxy',
            'type' => 'movie',
            'year' => 2014,
        ];

        $movie = $this->builder->buildFromOmdb($data);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testBuildWithNetflix()
    {
        $data = [
            'show_id' => 1234,
            'show_title' => 'Guardians of the Galaxy',
            'release_year' => 2014,
            'mediatype' => 0,
            'summary' => 'Superheros save the galaxy',
            'poster' => 'www.movieposters.com/guardians-of-the-galaxy',
        ];

        $expected = [
            'id' => 1234,
            'plot' => 'Superheros save the galaxy',
            'poster' => 'www.movieposters.com/guardians-of-the-galaxy',
            'title' => 'Guardians of the Galaxy',
            'type' => 'movie',
            'year' => 2014,
        ];

        $movie = $this->builder->buildFromNetflix($data);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $movie->provideMovieInterest());
    }
}
