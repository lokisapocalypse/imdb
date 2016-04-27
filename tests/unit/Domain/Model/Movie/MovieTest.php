<?php

namespace Fusani\Movies\Domain\Model\Movie;

use PHPUnit_Framework_TestCase;

class MovieTest extends PHPUnit_Framework_TestCase
{
    protected $expected;
    protected $movie;

    public function setup()
    {
        $this->expected = [
            'id' => 15,
            'title' => 'Guardians of the Galaxy',
            'year' => 2014,
            'type' => 'movie',
            'poster' => null,
            'plot' => null,
        ];
        $this->movie = new Movie(15, 'Guardians of the Galaxy', 'movie', 2014);
    }

    public function testProvideInterestSimpleObject()
    {
        $this->assertEquals($this->expected, $this->movie->provideMovieInterest());
    }

    public function testSetPoster()
    {
        $this->movie->setPoster('www.movieposters.com/guardians-of-the-galaxy');
        $expected = array_merge($this->expected, ['poster' => 'www.movieposters.com/guardians-of-the-galaxy']);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testSetPlot()
    {
        $this->movie->setPlot('Superheros save the world');
        $expected = array_merge($this->expected, ['plot' => 'Superheros save the world']);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }
}
