<?php

namespace Fusani\Movies\Domain\Model\Movie;

use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Domain\Model\Movie\Movie
 */
class MovieTest extends PHPUnit_Framework_TestCase
{
    protected $expected;
    protected $movie;

    public function setup()
    {
        $this->expected = [
            'id' => 15,
            'alternateTitles' => [],
            'cast' => [],
            'directors' => [],
            'episodes' => [],
            'title' => 'Guardians of the Galaxy',
            'year' => 2014,
            'rating' => null,
            'type' => 'movie',
            'sources' => [],
            'poster' => null,
            'plot' => null,
        ];
        $this->movie = new Movie(15, 'Guardians of the Galaxy', 'movie', 2014);
    }

    public function testAddAlternateTitles()
    {
        $this->movie->addAlternateTitle('Guardianes de la Galaxia');
        $interest = $this->movie->provideMovieInterest();
        $this->assertEquals(['Guardianes de la Galaxia'], $interest['alternateTitles']);

        $this->movie->addAlternateTitle('guardians qIb');
        $interest = $this->movie->provideMovieInterest();
        $this->assertEquals(['Guardianes de la Galaxia', 'guardians qIb'], $interest['alternateTitles']);
    }

    public function testAddCast()
    {
        $this->movie->addCast('Bill Murray');
        $expected = array_merge($this->expected, ['cast' => ['Bill Murray']]);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $this->movie->addCast('Dan Akroyd');
        $expected = array_merge($this->expected, ['cast' => ['Bill Murray', 'Dan Akroyd']]);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddDirector()
    {
        $this->movie->addDirector('Ivan Reitman');
        $expected = array_merge($this->expected, ['directors' => ['Ivan Reitman']]);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $this->movie->addDirector('Harold Ramis');
        $expected = array_merge($this->expected, ['directors' => ['Ivan Reitman', 'Harold Ramis']]);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddEpisode()
    {
        $episode = new Episode(15, 'Guardians of Galaxy', '2014-05-28', 1, 1);

        $this->movie->addEpisode($episode);

        $interest = $this->movie->provideMovieInterest();
        $this->assertEquals($interest, array_merge($this->expected, ['episodes' => [$episode->provideEpisodeInterest()]]));
    }

    public function testAddSource()
    {
        $interest = $this->movie->provideMovieInterest();
        $this->assertEquals([], $interest['sources']);

        $this->movie->addSource('free', 'Netflix', 'www.netflix.com');
        $this->movie->addSource('purchase', 'Amazon', 'www.amazon.com');

        $interest = $this->movie->provideMovieInterest();

        $this->assertNotEquals([], $interest['sources']);
    }

    public function testHasSourceWithNoSources()
    {
        $this->assertFalse($this->movie->hasSource('doesnt', 'exist'));
    }

    public function testHasSourceNoMatchingTypes()
    {
        $this->movie->addSource('free', 'Netflix', 'www.netflix.com');
        $this->assertFalse($this->movie->hasSource('Netflix', 'paid'));
    }

    public function testHasSourceNoMatchingNames()
    {
        $this->movie->addSource('free', 'Netflix', 'www.netflix.com');
        $this->assertFalse($this->movie->hasSource('Netflux', 'free'));
    }

    public function testHasSourceWithMatch()
    {
        $this->movie->addSource('free', 'Netflix', 'www.netflix.com');
        $this->assertTrue($this->movie->hasSource('Netflix', 'free'));
    }

    public function testIdentity()
    {
        $this->assertEquals(15, $this->movie->identity());
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

    public function testProvideInterestSortedSources()
    {
        $this->movie->addSource('subscription', 'Netflix', 'www.netflix.com');
        $this->movie->addSource('subscription', 'Amazon', 'www.amazon.com');
        $this->movie->addSource('subscription', 'VUDU', 'www.vudu.com');

        $expected = array_merge($this->expected, [
            'sources' => [
                'subscription' => [
                    [
                        'details' => [],
                        'link' => 'www.amazon.com',
                        'name' => 'Amazon',
                        'type' => 'subscription',
                    ],
                    [
                        'details' => [],
                        'link' => 'www.netflix.com',
                        'name' => 'Netflix',
                        'type' => 'subscription',
                    ],
                    [
                        'details' => [],
                        'link' => 'www.vudu.com',
                        'name' => 'VUDU',
                        'type' => 'subscription',
                    ],
                ],
            ],
        ]);

        $this->assertEquals($expected, $this->movie->provideMovieInterest());
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

    public function testSetRating()
    {
        $this->movie->setRating('PG-13');
        $expected = array_merge($this->expected, ['rating' => 'PG-13']);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testTitle()
    {
        $this->assertEquals('Guardians of the Galaxy', $this->movie->title());
    }
}
