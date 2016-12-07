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

    public function testAddAlternateTitlesFromTheMovieDBNoAlternateTitles()
    {
        $movie = new Movie(15, 'Ghostbusters', 'movie', 1984);
        $movie = $this->builder->addAlternateTitlesFromTheMovieDB($movie, []);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $this->assertEquals([], $movie->provideMovieInterest()['alternateTitles']);
    }

    public function testAddAlternateTitlesFromTheMovieDB()
    {
        $data = [
            ['title' => 'Le Ghostbusters'],
            ['title' => 'The Ghostbusters'],
        ];
        $movie = new Movie(15, 'Ghostbusters', 'movie', 1984);
        $movie = $this->builder->addAlternateTitlesFromTheMovieDB($movie, $data);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = ['Le Ghostbusters', 'The Ghostbusters'];
        $this->assertEquals($expected, $movie->provideMovieInterest()['alternateTitles']);
    }

    public function testAddCastFromTheMovieDBNoCast()
    {
        $movie = new Movie(15, 'Ghostbusters', 'movie', 1984);
        $movie = $this->builder->addCastFromTheMovieDB($movie, []);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $this->assertEquals([], $movie->provideMovieInterest()['cast']);
        $this->assertEquals([], $movie->provideMovieInterest()['crew']);
    }

    public function testAddCastFromTheMovieDB()
    {
        $data = [
            'cast' => [
                ['name' => 'Bill Murray', 'character' => 'Peter Venkman'],
                ['name' => 'Harold Ramis', 'character' => 'Egon Spangler'],
            ],
            'crew' => [
                ['name' => 'Ivan Reitman', 'job' => 'Director', 'department' => 'directors'],
                ['name' => 'Harold Ramis', 'job' => 'Writer', 'department' => 'writers'],
            ],
        ];

        $movie = new Movie(15, 'Ghostbusters', 'movie', 1984);
        $movie = $this->builder->addCastFromTheMovieDB($movie, $data);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = [
            'cast' => [
                ['actor' => 'Bill Murray', 'character' => 'Peter Venkman'],
                ['actor' => 'Harold Ramis', 'character' => 'Egon Spangler'],
            ],
            'crew' => [
                ['name' => 'Ivan Reitman', 'job' => 'Director', 'department' => 'directors'],
                ['name' => 'Harold Ramis', 'job' => 'Writer', 'department' => 'writers'],
            ],
        ];

        $this->assertEquals($expected['cast'], $movie->provideMovieInterest()['cast']);
        $this->assertEquals($expected['crew'], $movie->provideMovieInterest()['crew']);
    }

    public function testAddKeywordsFromTheMovieDBNoKeywords()
    {
        $movie = new Movie(15, 'Ghostbusters', 'movie', 1984);
        $movie = $this->builder->addKeywordsFromTheMovieDB($movie, []);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $this->assertEquals([], $movie->provideMovieInterest()['keywords']);
    }

    public function testAddKeywordsFromTheMovieDB()
    {
        $data = [
            ['name' => 'ghost'],
            ['name' => 'busting'],
        ];
        $movie = new Movie(15, 'Ghostbusters', 'movie', 1984);
        $movie = $this->builder->addKeywordsFromTheMovieDB($movie, $data);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = ['ghost', 'busting'];
        $this->assertEquals($expected, $movie->provideMovieInterest()['keywords']);
    }

    public function testAddRecommendationsFromTheMovieDBNoRecommendations()
    {
        $movie = new Movie(15, 'Ghostbusters', 'movie', 1984);
        $movie = $this->builder->addRecommendationsFromTheMovieDB($movie, []);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $this->assertEquals([], $movie->provideMovieInterest()['recommendations']);
    }

    public function testAddRecommendationsFromTheMovieDB()
    {
        $data = [
            ['id' => 16, 'release_date' => '1989-07-04', 'title' => 'Ghostbusters 2', 'overview' => 'I still aint afraid of no ghosts'],
            ['id' => 17, 'release_date' => '2016-04-14', 'title' => 'Ghostbusters', 'overview' => 'These chicks aint afraid of no ghosts'],
        ];

        $ghostbusters = new Movie(17, 'Ghostbusters', 'movie', '2016');
        $ghostbusters->setPlot('These chicks aint afraid of no ghosts');
        $ghostbusters->addExternalId(new ExternalId(17, 'The Movie DB'));
        $ghostbustersTwo = new Movie(16, 'Ghostbusters 2', 'movie', '1989');
        $ghostbustersTwo->setPlot('I still aint afraid of no ghosts');
        $ghostbustersTwo->addExternalId(new ExternalId(16, 'The Movie DB'));

        $movie = new Movie(15, 'Ghostbusters', 'movie', 1984);
        $movie = $this->builder->addRecommendationsFromTheMovieDB($movie, $data);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = [$ghostbustersTwo->provideMovieInterest(), $ghostbusters->provideMovieInterest()];
        $this->assertEquals($expected, $movie->provideMovieInterest()['recommendations']);
    }

    public function testAddReviewFromTheMovieDBNoReviews()
    {
        $movie = new Movie(15, 'Ghostbusters', 'movie', 1984);
        $movie = $this->builder->addReviewsFromTheMovieDB($movie, []);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $this->assertEquals([], $movie->provideMovieInterest()['reviews']);
    }

    public function testAddReviewFromTheMovieDB()
    {
        $data = [
            ['content' => 'It was good', 'author' => 'genius', 'url' => 'www.truth.com'],
            ['content' => 'It sucked', 'author' => 'idiot', 'url' => 'www.lies.com'],
        ];
        $movie = new Movie(15, 'Ghostbusters', 'movie', 1984);
        $movie = $this->builder->addReviewsFromTheMovieDB($movie, $data);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = [
            ['author' => 'genius', 'link' => 'www.truth.com', 'review' => 'It was good'],
            ['author' => 'idiot', 'link' => 'www.lies.com', 'review' => 'It sucked'],
        ];
        $this->assertEquals($expected, $movie->provideMovieInterest()['reviews']);
    }

    public function testAddSimilarMoviesFromTheMovieDBNoSimilarMovies()
    {
        $movie = new Movie(15, 'Ghostbusters', 'movie', 1984);
        $movie = $this->builder->addSimilarMoviesFromTheMovieDB($movie, []);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $this->assertEquals([], $movie->provideMovieInterest()['similarMovies']);
    }

    public function testAddSimilarMoviesFromTheMovieDB()
    {
        $data = [
            ['id' => 16, 'release_date' => '1989-07-04', 'title' => 'Ghostbusters 2', 'overview' => 'I still aint afraid of no ghosts'],
            ['id' => 17, 'release_date' => '2016-04-14', 'title' => 'Ghostbusters', 'overview' => 'These chicks aint afraid of no ghosts'],
        ];

        $ghostbusters = new Movie(17, 'Ghostbusters', 'movie', '2016');
        $ghostbusters->setPlot('These chicks aint afraid of no ghosts');
        $ghostbusters->addExternalId(new ExternalId(17, 'The Movie DB'));
        $ghostbustersTwo = new Movie(16, 'Ghostbusters 2', 'movie', '1989');
        $ghostbustersTwo->setPlot('I still aint afraid of no ghosts');
        $ghostbustersTwo->addExternalId(new ExternalId(16, 'The Movie DB'));

        $movie = new Movie(15, 'Ghostbusters', 'movie', '1984');
        $movie = $this->builder->addSimilarMoviesFromTheMovieDB($movie, $data);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = [$ghostbustersTwo->provideMovieInterest(), $ghostbusters->provideMovieInterest()];
        $this->assertEquals($expected, $movie->provideMovieInterest()['similarMovies']);
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
        $data = array_merge($this->guideBoxMovie(), ['first_aired' => '2014-05-26', 'release_year' => null, 'release_date' => '2015-05-26']);

        $movie = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals(2014, $interest['year']);
    }

    public function testBuildWithGuideboxUsesReleaseYearFirst()
    {
        $data = array_merge($this->guideBoxMovie(), ['first_aired' => '2014-05-26', 'release_year' => 2013, 'release_date' => '2015-05-26']);

        $movie = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals(2013, $interest['year']);
    }

    public function testBuildWithGuideboxUsesReleaseDateIfNothingElseIsAvailable()
    {
        $data = array_merge($this->guideBoxMovie(), ['first_aired' => null, 'release_year' => null, 'release_date' => '2015-05-26']);

        $movie = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals(2015, $interest['year']);
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
        $cast = [
            ['name' => 'Chris Pratt', 'character_name' => 'Starlord'],
            ['name' => 'Bradley Cooper', 'character_name' => 'Rocket Raccoon'],
        ];
        $data = array_merge($this->guideBoxMovie(), ['cast' => $cast]);

        $movie = $this->builder->buildFromGuidebox($data);
        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals(
            [
                ['actor' => 'Chris Pratt', 'character' => 'Starlord'],
                ['actor' => 'Bradley Cooper', 'character' => 'Rocket Raccoon']
            ],
            $interest['cast']
        );
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

    public function testBuildWithGuideboxSetRating()
    {
        $data = array_merge($this->guideBoxMovie(), ['rating' => 'PG-13']);
        $movie = $this->builder->buildFromGuidebox($data);
        $this->assertInstanceOf(Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals('PG-13', $interest['rating']);
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
            'Plot' => 'Superheros save the galaxy',
            'Poster' => 'N/A',
            'Type' => 'movie',
            'Year' => 2014,
            'imdbID' => 1234,
        ];

        $expected = array_merge($this->expected('OMDB'), ['poster' => null]);

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
            'imdbID' => 1234,
        ];

        $expected = array_merge($this->expected('OMDB'), ['plot' => null]);

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

        $expected = $this->expected('Netflix');

        $movie = $this->builder->buildFromNetflix($data);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testBuildWithTheMovieDB()
    {
        $movie = $this->builder->buildFromTheMovieDB($this->theMovieDB(), 'movie');

        $expected = array_merge($this->expected('The Movie DB'), ['poster' => null]);
        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testBuildWithTheMovieDBUsingFirstAirDate()
    {
        $data = array_merge($this->theMovieDB(), ['release_date' => null, 'first_air_date' => '2014-05-28']);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $expected = array_merge($this->expected('The Movie DB'), ['poster' => null]);
        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testBuildWithTheMovieDBUsingOriginalName()
    {
        $data = array_merge($this->theMovieDB(), ['title' => null, 'original_name' => 'Guardians of the Galaxy']);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $expected = array_merge($this->expected('The Movie DB'), ['poster' => null]);
        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testBuildWithTheMovieDBWithAnOriginalTitle()
    {
        $data = array_merge($this->theMovieDB(), ['original_title' => 'The Guardians of the Galaxy']);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $expected = array_merge($this->expected('The Movie DB'), ['poster' => null, 'alternateTitles' => ['The Guardians of the Galaxy']]);
        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testBuildWithTheMovieDBNameAloneDoesntAddAlternateTitles()
    {
        $data = array_merge($this->theMovieDB(), ['name' => 'The Guardians of the Galaxy']);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals([], $movie->provideMovieInterest()['alternateTitles']);
    }

    public function testBuildWithTheMovieDBOriginalNameAloneDoesntAddAlternateTitle()
    {
        $data = array_merge($this->theMovieDB(), ['original_name' => 'The Guardians of the Galaxy']);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals([], $movie->provideMovieInterest()['alternateTitles']);
    }

    public function testBuildWithTheMovieDBSameNameAndTitleDoesntAddAlternateTitle()
    {
        $data = array_merge($this->theMovieDB(), ['original_name' => 'The Guardians of the Galaxy', 'name' => 'The Guardians of the Galaxy']);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals([], $movie->provideMovieInterest()['alternateTitles']);
    }

    public function testBuildWithTheMovieDBDifferentNameAndTitleAddsAlternateTitle()
    {
        $data = array_merge($this->theMovieDB(), ['original_name' => 'Guardians of the Galaxy', 'name' => 'The Guardians of the Galaxy']);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals(['The Guardians of the Galaxy'], $movie->provideMovieInterest()['alternateTitles']);
    }

    public function testBuildWithTheMovieDBSetCollection()
    {
        $data = array_merge($this->theMovieDB(), ['belongs_to_collection' => ['name' => 'Marvel Collection']]);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals('Marvel Collection', $movie->provideMovieInterest()['collection']);
    }

    public function testBuildWithTheMovieDBSetBudget()
    {
        $data = array_merge($this->theMovieDB(), ['budget' => 40000000]);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals(40000000, $movie->provideMovieInterest()['budget']);
    }

    public function testBuildWithTheMovieDBAddGenres()
    {
        $data = array_merge($this->theMovieDB(), ['genres' => [['name' => 'Comic'], ['name' => 'Superhero'], ['name' => 'Science Fiction']]]);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals(['Comic', 'Superhero', 'Science Fiction'], $movie->provideMovieInterest()['genres']);
    }

    public function testBuildWithTheMovieDBSetHomepage()
    {
        $data = array_merge($this->theMovieDB(), ['homepage' => 'www.gotg.com']);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals('www.gotg.com', $movie->provideMovieInterest()['homepage']);
    }

    public function testBuildWithTheMovieDBSetImdb()
    {
        $data = array_merge($this->theMovieDB(), ['imdb_id' => 'imdb198412414']);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $expected = [
            ['source' => 'The Movie DB', 'externalId' => '1234'],
            ['source' => 'IMDB', 'externalId' => 'imdb198412414'],
        ];
        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $movie->provideMovieInterest()['externalIds']);
    }

    public function testBuildWithTheMovieDBSetLanguage()
    {
        $data = array_merge($this->theMovieDB(), ['original_language' => 'English']);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals(['English'], $movie->provideMovieInterest()['languages']);
    }

    public function testBuildWithTheMovieDBAddProductionCompanies()
    {
        $data = array_merge($this->theMovieDB(), ['production_companies' => [['name' => 'Marvel Studios'], ['name' => 'Disney']]]);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals(['Marvel Studios', 'Disney'], $movie->provideMovieInterest()['productionCompanies']);
    }

    public function testBuildWithTheMovieDBSetRevenue()
    {
        $data = array_merge($this->theMovieDB(), ['revenue' => 1231289401]);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals(1231289401, $movie->provideMovieInterest()['revenue']);
    }

    public function testBuildWithTheMovieDBSetRuntime()
    {
        $data = array_merge($this->theMovieDB(), ['runtime' => 144]);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals(144, $movie->provideMovieInterest()['runtime']);
    }

    public function testBuildWithTheMovieDBSetStatus()
    {
        $data = array_merge($this->theMovieDB(), ['status' => 'Awesome']);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals('Awesome', $movie->provideMovieInterest()['status']);
    }

    public function testBuildWithTheMovieDBSetTagline()
    {
        $data = array_merge($this->theMovieDB(), ['tagline' => 'They will guard the galaxy']);
        $movie = $this->builder->buildFromTheMovieDB($data, 'movie');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals('They will guard the galaxy', $movie->provideMovieInterest()['tagline']);
    }

    protected function expected($source)
    {
        $expected = [
            'id' => 1234,
            'alternateTitles' => [],
            'budget' => null,
            'cast' => [],
            'collection' => null,
            'crew' => [],
            'directors' => [],
            'episodes' => [],
            'externalIds' => [],
            'genres' => [],
            'homepage' => null,
            'keywords' => [],
            'languages' => [],
            'plot' => 'Superheros save the galaxy',
            'poster' => 'www.movieposters.com/guardians-of-the-galaxy',
            'productionCompanies' => [],
            'productionCountries' => [],
            'rating' => null,
            'recommendations' => [],
            'revenue' => null,
            'reviews' => [],
            'runtime' => null,
            'similarMovies' => [],
            'sources' => [],
            'status' => null,
            'tagline' => null,
            'title' => 'Guardians of the Galaxy',
            'type' => 'movie',
            'year' => 2014,
        ];

        $externalId = 0;
        if ($source == 'Guidebox') {
            $externalId = 15;
        } else {
            $externalId = 1234;
        }

        $expected['externalIds'][] = ['source' => $source, 'externalId' => $externalId];

        return $expected;
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

    protected function theMovieDB()
    {
        return [
            'id' => 1234,
            'title' => 'Guardians of the Galaxy',
            'release_date' => '2014-05-28',
            'overview' => 'Superheros save the galaxy',
        ];
    }
}
