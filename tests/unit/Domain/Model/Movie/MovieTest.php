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
            'plot' => null,
            'posters' => [],
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
        $movie = $this->movie->addCast('Bill Murray', 'Peter Venkman');
        $expected = array_merge($this->expected, ['cast' => [['actor' => 'Bill Murray', 'character' => 'Peter Venkman']]]);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $this->assertInstanceOf(Movie::class, $movie);

        $movie = $this->movie->addCast('Dan Akroyd', 'Raymond Stantz');
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = array_merge(
            $this->expected,
            [
                'cast' => [
                    ['actor' => 'Bill Murray', 'character' => 'Peter Venkman'],
                    ['actor' => 'Dan Akroyd', 'character' => 'Raymond Stantz']
                ]
            ]
        );

        $this->assertEquals(
            $expected['cast'],
            $this->movie->provideMovieWithSourcesConsolidatedInterest()['cast']
        );
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddCastDoesNotAddDuplicateCast()
    {
        $movie = $this->movie->addCast('Bill Murray', 'Peter Venkman');
        $expected = array_merge($this->expected, ['cast' => [['actor' => 'Bill Murray', 'character' => 'Peter Venkman']]]);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $this->assertInstanceOf(Movie::class, $movie);

        $movie = $this->movie->addCast('Bill Murray', 'Peter Venkman');
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = array_merge(
            $this->expected,
            [
                'cast' => [
                    ['actor' => 'Bill Murray', 'character' => 'Peter Venkman'],
                ]
            ]
        );

        $this->assertEquals(
            $expected['cast'],
            $this->movie->provideMovieWithSourcesConsolidatedInterest()['cast']
        );
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddCrew()
    {
        $movie = $this->movie->addCrew('Ivan Reitman', 'Director', 'directors');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $movie = $this->movie->addCrew('Harold Ramis', 'Writer', 'writers');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = array_merge(
            $this->expected,
            [
                'crew' => [
                    ['name' => 'Ivan Reitman', 'job' => 'Director', 'department' => 'directors'],
                    ['name' => 'Harold Ramis', 'job' => 'Writer', 'department' => 'writers'],
                ],
            ]
        );

        $this->assertEquals(
            $expected['crew'],
            $this->movie->provideMovieWithSourcesConsolidatedInterest()['crew']
        );
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddCrewDoesntAddDuplicateCrew()
    {
        $movie = $this->movie->addCrew('Ivan Reitman', 'Director', 'directors');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $movie = $this->movie->addCrew('Ivan Reitman', 'Director', 'directors');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = array_merge(
            $this->expected,
            [
                'crew' => [
                    ['name' => 'Ivan Reitman', 'job' => 'Director', 'department' => 'directors'],
                ],
            ]
        );

        $this->assertEquals(
            $expected['crew'],
            $this->movie->provideMovieWithSourcesConsolidatedInterest()['crew']
        );
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddDirector()
    {
        $movie = $this->movie->addDirector('Ivan Reitman');
        $expected = array_merge($this->expected, ['directors' => ['Ivan Reitman']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $movie = $this->movie->addDirector('Harold Ramis');
        $expected = array_merge($this->expected, ['directors' => ['Ivan Reitman', 'Harold Ramis']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddDirectorDoesntAddDuplicateDirectors()
    {
        $movie = $this->movie->addDirector('Ivan Reitman');
        $expected = array_merge($this->expected, ['directors' => ['Ivan Reitman']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $movie = $this->movie->addDirector('Ivan Reitman');
        $expected = array_merge($this->expected, ['directors' => ['Ivan Reitman']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddEpisode()
    {
        $episode = new Episode(15, 'Guardians of Galaxy', '2014-05-28', 1, 1);

        $movie = $this->movie->addEpisode($episode);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $interest = $this->movie->provideMovieInterest();
        $this->assertEquals($interest, array_merge($this->expected, ['episodes' => [$episode->provideEpisodeInterest()]]));

        $episodeTwo = new Episode(16, 'Guardians of the Galaxy', '2017-05-28', 1, 2);

        $movie = $this->movie->addEpisode($episodeTwo);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $interest = $this->movie->provideMovieInterest();

        $this->assertEquals(
            $interest,
            array_merge(
                $this->expected,
                ['episodes' => [$episode->provideEpisodeInterest(), $episodeTwo->provideEpisodeInterest()]]
            )
        );
    }

    public function testAddEpisodeDoNotAddDuplicateEpisode()
    {
        $episode = new Episode(15, 'Guardians of Galaxy', '2014-05-28', 1, 1);

        $movie = $this->movie->addEpisode($episode);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $interest = $this->movie->provideMovieInterest();
        $this->assertEquals($interest, array_merge($this->expected, ['episodes' => [$episode->provideEpisodeInterest()]]));

        $episodeTwo = new Episode(16, 'Guardians of the Galaxy', '2017-05-28', 1, 1);

        $movie = $this->movie->addEpisode($episode);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $interest = $this->movie->provideMovieInterest();

        $this->assertEquals(
            $interest,
            array_merge(
                $this->expected,
                ['episodes' => [$episode->provideEpisodeInterest()]]
            )
        );
    }

    public function testAddExternalId()
    {
        $movie = $this->movie->addExternalId('imdb15124', 'imdb');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $movie = $this->movie->addExternalId('ttmb194810', 'theMovieDB');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = array_merge(
            $this->expected,
            [
                'externalIds' => [
                    ['externalId' => 'imdb15124', 'source' => 'imdb'],
                    ['externalId' => 'ttmb194810', 'source' => 'theMovieDB'],
                ],
            ]
        );

        $this->assertEquals(
            $expected['externalIds'],
            $this->movie->provideMovieWithSourcesConsolidatedInterest()['externalIds']
        );
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddExternalIdDoesntAddDuplicateExternalId()
    {
        $movie = $this->movie->addExternalId('imdb15124', 'imdb');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $movie = $this->movie->addExternalId('imdb15124', 'imdb');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = array_merge(
            $this->expected,
            [
                'externalIds' => [
                    ['externalId' => 'imdb15124', 'source' => 'imdb'],
                ],
            ]
        );

        $this->assertEquals(
            $expected['externalIds'],
            $this->movie->provideMovieWithSourcesConsolidatedInterest()['externalIds']
        );
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddGenre()
    {
        $movie = $this->movie->addGenre('comedy');
        $expected = array_merge($this->expected, ['genres' => ['comedy']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $movie = $this->movie->addGenre('supernatural');
        $expected = array_merge($this->expected, ['genres' => ['comedy', 'supernatural']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddGenreDoesntAddDuplicateGenres()
    {
        $movie = $this->movie->addGenre('comedy');
        $expected = array_merge($this->expected, ['genres' => ['comedy']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $movie = $this->movie->addGenre('comedy');
        $expected = array_merge($this->expected, ['genres' => ['comedy']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddKeyword()
    {
        $movie = $this->movie->addKeyword('ghost');
        $expected = array_merge($this->expected, ['keywords' => ['ghost']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $movie = $this->movie->addKeyword('supernatural');
        $expected = array_merge($this->expected, ['keywords' => ['ghost', 'supernatural']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddKeywordDoesntAddDuplicateKeywords()
    {
        $movie = $this->movie->addKeyword('ghost');
        $expected = array_merge($this->expected, ['keywords' => ['ghost']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $movie = $this->movie->addKeyword('ghost');
        $expected = array_merge($this->expected, ['keywords' => ['ghost']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddLanguage()
    {
        $movie = $this->movie->addLanguage('english');
        $expected = array_merge($this->expected, ['languages' => ['english']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $movie = $this->movie->addLanguage('klingon');
        $expected = array_merge($this->expected, ['languages' => ['english', 'klingon']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddLanguageDoesntAddDuplicateLanguages()
    {
        $movie = $this->movie->addLanguage('english');
        $expected = array_merge($this->expected, ['languages' => ['english']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $movie = $this->movie->addLanguage('english');
        $expected = array_merge($this->expected, ['languages' => ['english']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddPosterWithNoPosters()
    {
        $movie = $this->movie->addPoster('www.ghostbusters.com/poster', 'poster', '208x117');
        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $movie = $this->movie->addPoster('www.ghostbusters.com/banner', 'banner', '700x380');
        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = [
            ['link' => 'www.ghostbusters.com/poster', 'type' => 'poster', 'size' => '208x117'],
            ['link' => 'www.ghostbusters.com/banner', 'type' => 'banner', 'size' => '700x380'],
        ];

        $this->assertEquals($expected, $movie->provideMovieInterest()['posters']);
        $this->assertEquals($expected, $movie->provideMovieWithSourcesConsolidatedInterest()['posters']);
    }

    public function testAddDuplicatePoster()
    {
        $movie = $this->movie->addPoster('www.ghostbusters.com/poster', 'poster', '208x117');
        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $movie = $this->movie->addPoster('www.ghostbusters.com/poster', 'poster', '208x117');
        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = [
            ['link' => 'www.ghostbusters.com/poster', 'type' => 'poster', 'size' => '208x117'],
        ];

        $this->assertEquals($expected, $movie->provideMovieInterest()['posters']);
        $this->assertEquals($expected, $movie->provideMovieWithSourcesConsolidatedInterest()['posters']);
    }

    public function testAddProductionCompany()
    {
        $movie = $this->movie->addProductionCompany('Universal');
        $expected = array_merge($this->expected, ['productionCompanies' => ['Universal']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $movie = $this->movie->addProductionCompany('Fox');
        $expected = array_merge($this->expected, ['productionCompanies' => ['Universal', 'Fox']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddProductionCompanyDoesntAddDuplicateProductionCompanys()
    {
        $movie = $this->movie->addProductionCompany('Universal');
        $expected = array_merge($this->expected, ['productionCompanies' => ['Universal']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $movie = $this->movie->addProductionCompany('Universal');
        $expected = array_merge($this->expected, ['productionCompanies' => ['Universal']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddProductionCountry()
    {
        $movie = $this->movie->addProductionCountry('USA');
        $expected = array_merge($this->expected, ['productionCountries' => ['USA']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $movie = $this->movie->addProductionCountry('Mordor');
        $expected = array_merge($this->expected, ['productionCountries' => ['USA', 'Mordor']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddProductionCountryDoesntAddDuplicateProductionCountrys()
    {
        $movie = $this->movie->addProductionCountry('USA');
        $expected = array_merge($this->expected, ['productionCountries' => ['USA']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());

        $movie = $this->movie->addProductionCountry('USA');
        $expected = array_merge($this->expected, ['productionCountries' => ['USA']]);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddRecommendation()
    {
        $ghostbustersTwo = new Movie(15, 'Ghostbusters 2', 'movie', 1989);
        $newGhostbusters = new Movie(16, 'Ghostbusters', 'movie', 2016);

        $movie = $this->movie->addRecommendation($ghostbustersTwo);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $movie = $this->movie->addRecommendation($newGhostbusters);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = array_merge(
            $this->expected,
            [
                'recommendations' => [
                    $ghostbustersTwo->provideMovieInterest(),
                    $newGhostbusters->provideMovieInterest(),
                ],
            ]
        );

        $this->assertEquals(
            $expected['recommendations'],
            $this->movie->provideMovieWithSourcesConsolidatedInterest()['recommendations']
        );
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddRecommendationDoesntAddDuplicateRecommendation()
    {
        $ghostbustersTwo = new Movie(15, 'Ghostbusters 2', 'movie', 1989);

        $movie = $this->movie->addRecommendation($ghostbustersTwo);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $movie = $this->movie->addRecommendation($ghostbustersTwo);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = array_merge(
            $this->expected,
            [
                'recommendations' => [
                    $ghostbustersTwo->provideMovieInterest(),
                ],
            ]
        );

        $this->assertEquals(
            $expected['recommendations'],
            $this->movie->provideMovieWithSourcesConsolidatedInterest()['recommendations']
        );
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddReview()
    {
        $reviewOne = new Review('Me', 'It rules', 'www.truth.org');
        $reviewTwo = new Review('Idiot', 'It sucks', 'www.moron.com');

        $movie = $this->movie->addReview('Me', 'It rules', 'www.truth.org');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $movie = $this->movie->addReview('Idiot', 'It sucks', 'www.moron.com');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = array_merge(
            $this->expected,
            [
                'reviews' => [
                    $reviewOne->provideReviewInterest(),
                    $reviewTwo->provideReviewInterest(),
                ],
            ]
        );

        $this->assertEquals(
            $expected['reviews'],
            $this->movie->provideMovieWithSourcesConsolidatedInterest()['reviews']
        );
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddReviewDoesntAddDuplicateReview()
    {
        $reviewOne = new Review('Ivan Reitman', 'reviewOne', 'reviewOnes');

        $movie = $this->movie->addReview('Ivan Reitman', 'reviewOne', 'reviewOnes');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $movie = $this->movie->addReview('Ivan Reitman', 'reviewOne', 'reviewOnes');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = array_merge(
            $this->expected,
            [
                'reviews' => [
                    $reviewOne->provideReviewInterest(),
                ],
            ]
        );

        $this->assertEquals(
            $expected['reviews'],
            $this->movie->provideMovieWithSourcesConsolidatedInterest()['reviews']
        );
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddSimilarMovie()
    {
        $ghostbustersTwo = new Movie(15, 'Ghostbusters 2', 'movie', 1989);
        $newGhostbusters = new Movie(16, 'Ghostbusters', 'movie', 2016);

        $movie = $this->movie->addSimilarMovie($ghostbustersTwo);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $movie = $this->movie->addSimilarMovie($newGhostbusters);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = array_merge(
            $this->expected,
            [
                'similarMovies' => [
                    $ghostbustersTwo->provideMovieInterest(),
                    $newGhostbusters->provideMovieInterest(),
                ],
            ]
        );

        $this->assertEquals(
            $expected['similarMovies'],
            $this->movie->provideMovieWithSourcesConsolidatedInterest()['similarMovies']
        );
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testAddSimilarMovieDoesntAddDuplicateSimilarMovie()
    {
        $ghostbustersTwo = new Movie(15, 'Ghostbusters 2', 'movie', 1989);

        $movie = $this->movie->addSimilarMovie($ghostbustersTwo);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $movie = $this->movie->addSimilarMovie($ghostbustersTwo);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie::class, $movie);

        $expected = array_merge(
            $this->expected,
            [
                'similarMovies' => [
                    $ghostbustersTwo->provideMovieInterest(),
                ],
            ]
        );

        $this->assertEquals(
            $expected['similarMovies'],
            $this->movie->provideMovieWithSourcesConsolidatedInterest()['similarMovies']
        );
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
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

    public function testProvideMovieWithSourcesConsolidatedInterest()
    {
        $this->movie->addSource('subscription', 'Netflix', 'www.netflix.com');
        $this->movie->addSource('free', 'Netflix', 'www.netflix.com');
        $this->movie->addSource('subscription', 'Amazon', 'www.amazon.com');
        $this->movie->addSource('tvEverywhere', 'Amazon', 'www.amazon.com');
        $this->movie->addSource('tvEverywhere', 'Netflix', 'www.netflix.com');
        $this->movie->addSource('purchase', 'Amazon', 'www.amazon.com');
        $this->movie->addSource('purchase', 'Netflix', 'www.netflix.com');
        $this->movie->addSource('purchase', 'Netfli', 'www.netflix.com');
        $this->movie->addSource('purchase', 'Netfl', 'www.netflix.com');
        $this->movie->addSource('purchase', 'Netf', 'www.netflix.com');
        $this->movie->addSource('purchase', 'Net', 'www.netflix.com');
        $this->movie->addSource('subscription', 'VUDU', 'www.vudu.com');

        $episode = new Episode(15, 'Guardians of Galaxy', '2014-05-28', 1, 1);

        $this->movie->addEpisode($episode);

        $expected = array_merge($this->expected, [
            'episodes' => [$episode->provideEpisodeInterest()],
            'sources' => [
                [
                    'details' => [],
                    'link' => 'www.netflix.com',
                    'name' => 'Netflix',
                    'type' => 'free',
                ],
                [
                    'details' => [],
                    'link' => 'www.amazon.com',
                    'name' => 'Amazon',
                    'type' => 'tvEverywhere',
                ],
                [
                    'details' => [],
                    'link' => 'www.netflix.com',
                    'name' => 'Netflix',
                    'type' => 'tvEverywhere',
                ],
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
                [
                    'details' => [],
                    'link' => 'www.amazon.com',
                    'name' => 'Amazon',
                    'type' => 'purchase',
                ],
                [
                    'details' => [],
                    'link' => 'www.netflix.com',
                    'name' => 'Net',
                    'type' => 'purchase',
                ],
                [
                    'details' => [],
                    'link' => 'www.netflix.com',
                    'name' => 'Netf',
                    'type' => 'purchase',
                ],
                [
                    'details' => [],
                    'link' => 'www.netflix.com',
                    'name' => 'Netfl',
                    'type' => 'purchase',
                ],
                [
                    'details' => [],
                    'link' => 'www.netflix.com',
                    'name' => 'Netfli',
                    'type' => 'purchase',
                ],
                [
                    'details' => [],
                    'link' => 'www.netflix.com',
                    'name' => 'Netflix',
                    'type' => 'purchase',
                ],
            ],
        ]);

        $this->assertEquals($expected, $this->movie->provideMovieWithSourcesConsolidatedInterest());
    }

    public function testSetBudget()
    {
        $this->movie->setBudget(40000000);
        $this->assertEquals(40000000, $this->movie->provideMovieInterest()['budget']);
    }

    public function testSetCollection()
    {
        $this->movie->setCollection('Marvel Studios');
        $this->assertEquals('Marvel Studios', $this->movie->provideMovieInterest()['collection']);
    }

    public function testSetHomepage()
    {
        $this->movie->setHomepage('www.gotg.com');
        $this->assertEquals('www.gotg.com', $this->movie->provideMovieInterest()['homepage']);
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

    public function testSetRevenue()
    {
        $this->movie->setRevenue(229242989);
        $expected = array_merge($this->expected, ['revenue' => 229242989]);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testSetRuntime()
    {
        $this->movie->setRuntime(115);
        $expected = array_merge($this->expected, ['runtime' => 115]);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testSetStatus()
    {
        $this->movie->setStatus('Released');
        $this->assertEquals('Released', $this->movie->provideMovieInterest()['status']);
    }

    public function testSetTagline()
    {
        $this->movie->setTagline('I aint afraid of no ghosts');
        $expected = array_merge($this->expected, ['tagline' => 'I aint afraid of no ghosts']);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testSetTitle()
    {
        $this->movie->setTitle('Ghostbusters');
        $expected = array_merge($this->expected, ['title' => 'Ghostbusters']);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testSetType()
    {
        $this->movie->setType('best movie ever');
        $expected = array_merge($this->expected, ['type' => 'best movie ever']);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testSetYear()
    {
        $this->movie->setYear(2016);
        $expected = array_merge($this->expected, ['year' => 2016]);
        $this->assertEquals($expected, $this->movie->provideMovieInterest());
    }

    public function testTitle()
    {
        $this->assertEquals('Guardians of the Galaxy', $this->movie->title());
    }
}
