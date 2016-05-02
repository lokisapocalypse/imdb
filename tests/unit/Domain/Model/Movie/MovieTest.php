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
            'sources' => [],
            'poster' => null,
            'plot' => null,
        ];
        $this->movie = new Movie(15, 'Guardians of the Galaxy', 'movie', 2014);
    }

    public function testIsTheSameAsWildlyMismatched()
    {
        $movie = new Movie(16, 'Spectacular Spider-Man', 'tvshow', 2008);
        $this->assertFalse($this->movie->isTheSameAs($movie));
    }

    public function testIsTheSameMatchingTitleButWrongYear()
    {
        $movie = new Movie(15, 'Guardians of the Galaxy', 'movie', 2008);
        $this->assertFalse($this->movie->isTheSameAs($movie));
    }

    public function testIsTheSameMatchingYearButWrongTitle()
    {
        $movie = new Movie(15, 'Guardians of the Galaxy II', 'movie', 2014);
        $this->assertFalse($this->movie->isTheSameAs($movie));
    }

    public function testIsTheSameMatching()
    {
        $movie = new Movie(16, 'Guardians of the Galaxy', 'movie', 2014);
        $this->assertTrue($this->movie->isTheSameAs($movie));
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

    public function testTitle()
    {
        $this->assertEquals('Guardians of the Galaxy', $this->movie->title());
    }

    public function testUpdatedWith()
    {
        $movie = new Movie(16, 'Guardians of the Galaxy', 'movie', 2014);
        $this->movie->updateWith('Netflix', $movie);

        $expected = array_merge($this->expected, ['sources' => ['netflix' => $movie->provideMovieInterest()]]);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }
}
