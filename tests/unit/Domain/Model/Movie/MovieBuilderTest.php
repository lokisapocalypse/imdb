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

    public function testBuildWithGuideboxWithAlternateTitles()
    {
        $data = array_merge($this->guideBoxMovie(), ['alternate_titles' => ['Guardianes de la Galaxia', 'guardians qIb']]);
        $movie = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals(['Guardianes de la Galaxia', 'guardians qIb'], $interest['alternateTitles']);
    }

    public function testBuildWithGuideboxIsMovieWithNoTVrageId()
    {
        $data = array_merge($this->guideBoxMovie(), ['tvrage' => ['tvrage_id' => null]]);

        $movie = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals('movie', $interest['type']);
    }

    public function testBuildWithGuideboxIsAMovieWithFlag()
    {
        $data = array_merge($this->guideBoxMovie(), ['isMovie' => 1]);

        $movie = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals('movie', $interest['type']);
    }

    public function testBuildWithGuideboxIsATvShow()
    {
        $data = array_merge($this->guideBoxMovie(), ['tvrage' => ['tvrage_id' => 15]]);

        $movie = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals('tvshow', $interest['type']);
    }

    public function testBuildWithGuideboxFirstAiredSet()
    {
        $data = array_merge($this->guideBoxMovie(), ['first_aired' => '2014-05-26', 'release_year' => null]);

        $movie = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals(2014, $interest['year']);
    }

    public function testBuildWithGuideboxWithArtwork()
    {
        $data = array_merge($this->guideBoxMovie(), ['artwork_208x117' => 'my poster', 'poster_120x171' => null]);

        $movie = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals('my poster', $interest['poster']);
    }

    public function testBuildWithGuideboxSetPlot()
    {
        $data = array_merge($this->guideBoxMovie(), ['overview' => 'Superheros save the world']);

        $movie = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals('Superheros save the world', $interest['plot']);
    }

    public function testBuildWithGuideboxSetCast()
    {
        $cast = [['name' => 'Chris Pratt'], ['name' => 'Bradley Cooper']];
        $data = array_merge($this->guideBoxMovie(), ['cast' => $cast]);

        $movie = $this->builder->buildFromGuidebox($data);
        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals(['Chris Pratt', 'Bradley Cooper'], $interest['cast']);
    }

    public function testBuildWithGuideboxSetDirectors()
    {
        $directors = [['name' => 'James Gunn'], ['name' => 'Stan Lee']];
        $data = array_merge($this->guideBoxMovie(), ['directors' => $directors]);

        $movie = $this->builder->buildFromGuidebox($data);
        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals(['James Gunn', 'Stan Lee'], $interest['directors']);
    }

    public function testBuildWithGuideboxHasFreeSources()
    {
        $data = array_merge($this->guideBoxMovie(), ['free_web_sources' => [['display_name' => 'Netflix', 'link' => 'www.netflix.com']]]);

        $movie = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertNotEmpty($interest['sources']['free']);
    }

    public function testBuildWithGuideboxHasTvEverywhereSources()
    {
        $data = array_merge($this->guideBoxMovie(), ['tv_everywhere_web_sources' => [['display_name' => 'Netflix', 'link' => 'www.netflix.com']]]);

        $movie = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertNotEmpty($interest['sources']['tvEverywhere']);
    }

    public function testBuildWithGuideboxHasSubscriptionSources()
    {
        $data = array_merge($this->guideBoxMovie(), ['subscription_web_sources' => [['display_name' => 'Netflix', 'link' => 'www.netflix.com']]]);

        $movie = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertNotEmpty($interest['sources']['subscription']);
    }

    public function testBuildWithGuideboxHasPurchaseSources()
    {
        $data = array_merge($this->guideBoxMovie(), ['purchase_web_sources' => [['display_name' => 'Netflix', 'link' => 'www.netflix.com']]]);

        $movie = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertNotEmpty($interest['sources']['purchase']);
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
            'alternateTitles' => [],
            'cast' => [],
            'directors' => [],
            'episodes' => [],
            'plot' => 'Superheros save the world',
            'poster' => null,
            'sources' => [],
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
            'alternateTitles' => [],
            'cast' => [],
            'directors' => [],
            'plot' => null,
            'episodes' => [],
            'poster' => 'www.movieposters.com/guardians-of-the-galaxy',
            'title' => 'Guardians of the Galaxy',
            'sources' => [],
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
            'alternateTitles' => [],
            'cast' => [],
            'directors' => [],
            'episodes' => [],
            'plot' => 'Superheros save the galaxy',
            'poster' => 'www.movieposters.com/guardians-of-the-galaxy',
            'title' => 'Guardians of the Galaxy',
            'sources' => [],
            'type' => 'movie',
            'year' => 2014,
        ];

        $movie = $this->builder->buildFromNetflix($data);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    protected function guideBoxMovie()
    {
        return [
            'id' => 15,
            'title' => 'Guardians of the Galaxy',
            'release_year' => 2014,
            'poster_120x171' => 'www.movieposters.com',
        ];
    }
}
