<?php

namespace Fusani\Movies\Infrastructure\Persistence\TheMovieDB;

use Fusani\Movies\Domain\Model\Movie;
use Fusani\Movies\Infrastructure\Adapter;
use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Infrastructure\Persistence\TheMovieDB\MovieRepository
 */
class MovieRepositoryTest extends PHPUnit_Framework_TestCase
{
    protected $adapter;
    protected $repository;

    public function setup()
    {
        $this->adapter = $this->getMockBuilder(Adapter\Adapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repository = new MovieRepository($this->adapter, 'derp', 'en');
    }

    public function testDoNotTryFuzzyOnFailMakesOneRequestToApi()
    {
        $repository = $this->repository->tryFuzzyOnFail()
            ->doNotTryFuzzyOnFail();

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['results' => []]));

        $this->setExpectedException(Movie\NotFoundException::class);
        $movie = $this->repository->oneOfTitle('Guardians of the Galaxy I', 2018);
    }

    public function testDoNotTryFuzzyOnFailReturnsSelf()
    {
        $repository = $this->repository->doNotTryFuzzyOnFail();
        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);
    }

    public function testManyWithNoMatches()
    {
        $this->adapter->expects($this->exactly(25))
            ->method('get')
            ->will($this->returnValue(['status_code' => 34]));

        $movies = $this->repository->many(0, 25, 'movie');

        $this->assertEquals([], $movies);
    }

    public function testManyWithMovieMatchesOnMovies()
    {
        $results = $this->apiResults();

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['movie/1'], ['movie/2'])
            ->will($this->onConsecutiveCalls($results[0], $results[1]));

        $movies = $this->repository->many(1, 2, 'movie');

        $this->assertEquals(2, count($movies));

        foreach ($movies as $movie) {
            $this->assertNotNull($movie);
            $this->assertInstanceOf(Movie\Movie::class, $movie);
        }
    }

    public function testManyWithMovieMatchesOnShows()
    {
        $results = $this->apiResults();

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['tv/1'], ['tv/2'])
            ->will($this->onConsecutiveCalls($results[0], $results[1]));

        $movies = $this->repository->many(1, 2, 'tv');

        $this->assertEquals(2, count($movies));

        foreach ($movies as $movie) {
            $this->assertNotNull($movie);
            $this->assertInstanceOf(Movie\Movie::class, $movie);
        }
    }

    public function testManyEpisodesOfShowDoesNotAddEpisodesIfNoApiResult()
    {
        $movie = new Movie\Movie(1, 'Ghostbusters', 'tv', 1984);
        $this->assertEquals([], $movie->provideMovieInterest()['episodes']);

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['tv/1/season/1/episode/1'], ['tv/1/season/1/episode/2'])
            ->will($this->returnValue(['status_code' => 34]));

        $result = $this->repository->manyEpisodesOfShow($movie, 1, false, false, 1, 1, 2);

        $this->assertEquals([], $result->provideMovieInterest()['episodes']);
    }

    public function testManyEpisodesOfShowAddsNewEpisodes()
    {
        $movie = new Movie\Movie(1, 'Ghostbusters', 'tv', 1984);
        $this->assertEquals([], $movie->provideMovieInterest()['episodes']);

        $episodeOne = [
            'id' => 1,
            'name' => 'Who you gonna call?',
            'air_date' => '1984-07-04',
            'season_number' => 1,
            'episode_number' => 1,
            'crew' => [
                ['name' => 'Ivan Reitman', 'job' => 'director', 'department' => 'Director'],
                ['name' => 'Harold Ramis', 'job' => 'writer', 'department' => 'Writers'],
            ],
            'guest_stars' => [
                ['name' => 'Bill Murray', 'character' => 'Peter Venkman'],
                ['name' => 'Dan Akroyd', 'character' => 'Raymond Stantz'],
            ],
            'overview' => 'Ghostbusters assemble',
        ];
        $episodeTwo = [
            'id' => 2,
            'name' => 'Rise of the slimer',
            'air_date' => '1984-07-11',
            'season_number' => 1,
            'episode_number' => 2,
            'crew' => [
                ['name' => 'Joe Lighter', 'job' => 'Head lighter', 'department' => 'Lights'],
                ['name' => 'Beverly Lighter', 'job' => 'Assitant lighter', 'department' => 'Lights'],
            ],
            'guest_stars' => [
                ['name' => 'Harold Ramis', 'character' => 'Egon Spangler'],
                ['name' => 'Ernie Hudson', 'character' => 'Winston Zedmore'],
            ],
            'overview' => 'Ghostbusters vs slimer',
        ];

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['tv/1/season/1/episode/1'], ['tv/1/season/1/episode/2'])
            ->will($this->onConsecutiveCalls($episodeOne, $episodeTwo));
        $result = $this->repository->manyEpisodesOfShow($movie, 1, false, false, 1, 1, 2);

        $this->assertEquals(2, count($result->provideMovieInterest()['episodes']));
    }

    public function testManyWithTitleNoMatchesReturnsEmptyArray()
    {
        $this->adapter->expects($this->once())
            ->method('get')
            ->withConsecutive(['search/movie'])
            ->will($this->returnValue(['results' => []]));

        $movies = $this->repository->manyWithTitle('Ghostbusters');

        $this->assertEquals([], $movies);
    }

    public function testManyWithTitleWithResultsButDoesntMatchTitleReturnsEmptyArray()
    {
        $this->adapter->expects($this->once())
            ->method('get')
            ->withConsecutive(['search/movie'])
            ->will($this->returnValue(['results' => [['original_title' => 'Ghosbusters 2'], ['original_title' => 'New Ghostbusters']]]));

        $movies = $this->repository->manyWithTitle('Ghostbusters');

        $this->assertEquals([], $movies);
    }

    public function testManyWithTitleMatchesDespiteCaseMismatch()
    {
        $movies = array_merge($this->apiResults(), $this->apiResults());
        $movies[0]['original_title'] = 'GhOsTbUsTERs';
        $movies[1]['original_title'] = 'ghostBUSTERS';

        $this->adapter->expects($this->once())
            ->method('get')
            ->withConsecutive(['search/movie'])
            ->will($this->returnValue(['results' => [$movies[0], $movies[1], $movies[2], $movies[3]]]));

        $movies = $this->repository->manyWithTitle('Ghostbusters');

        $this->assertEquals(3, count($movies));

        foreach ($movies as $movie) {
            $this->assertNotNull($movie);
            $this->assertInstanceOf(Movie\Movie::class, $movie);
        }
    }

    public function testManyWithTitleLikeNoMatchesReturnsEmptyArray()
    {
        $this->adapter->expects($this->once())
            ->method('get')
            ->withConsecutive(['search/movie'])
            ->will($this->returnValue(['results' => []]));

        $movies = $this->repository->manyWithTitleLike('Ghostbusters');

        $this->assertEquals([], $movies);
    }

    public function testManyWithTitleLikeMatchesDespiteCaseMismatch()
    {
        $movies = array_merge($this->apiResults(), $this->apiResults());
        $movies[0]['original_title'] = 'GhOsTbUsTERs';
        $movies[1]['original_title'] = 'ghostBUSTERS';

        $this->adapter->expects($this->once())
            ->method('get')
            ->withConsecutive(['search/movie'])
            ->will($this->returnValue(['results' => [$movies[0], $movies[1], $movies[2], $movies[3]]]));

        $movies = $this->repository->manyWithTitleLike('Ghostbusters');

        $this->assertEquals(4, count($movies));

        foreach ($movies as $movie) {
            $this->assertNotNull($movie);
            $this->assertInstanceOf(Movie\Movie::class, $movie);
        }
    }

    public function testOneOfIdNoMatchThrowsException()
    {
        $this->setExpectedException(Movie\NotFoundException::class);
        $this->adapter->expects($this->once())
            ->method('get')
            ->with('movie/15')
            ->will($this->returnValue(['status_code' => 34]));

        $this->repository->oneOfId(15);
    }

    public function testOneOfIdReturnsMovieObject()
    {
        $this->adapter->expects($this->once())
            ->method('get')
            ->with('movie/15')
            ->will($this->returnValue($this->apiResults()[0]));

        $movie = $this->repository->oneOfId(15);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
    }

    public function testOneOfTitleNoMatchesThrowsException()
    {
        $this->setExpectedException(Movie\NotFoundException::class);
        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue([]));

        $this->repository->oneOfTitle('Ghostbusters');
    }

    public function testOneOfTitleWithNoYearProvideMatchesMovieWithoutYearMatch()
    {
        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['results' => $this->apiResults()]));

        $movie = $this->repository->oneOfTitle('Ghostbusters');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
    }

    public function testOneOfTitleWithYearMismatchThrowsException()
    {
        $this->setExpectedException(Movie\NotFoundException::class);
        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['results' => $this->apiResults()]));

        $this->repository->oneOfTitle('Ghostbusters', 2016);
    }

    public function testOneOfTitleWithYearMatchesIfYearMatches()
    {
        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['results' => $this->apiResults()]));

        $movie = $this->repository->oneOfTitle('Ghostbusters', 1984);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
    }

    public function testOneOfTitleMatchesSimilarNameIfTryFuzzyIsEnabled()
    {
        $results = $this->apiResults();
        $results[0]['original_name'] = $results[0]['original_title'] = 'New Ghostbuters';

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValue(['results' => $results]));

        $movie = $this->repository->tryFuzzyOnFail()
                    ->setThreshold(5)
                    ->oneOfTitle('Ghostbusters');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
        $this->assertEquals('Ghostbusters II', $movie->provideMovieInterest()['title']);
    }

    public function testOneOfTitleThrowsExceptionOnFuzzySearchIfYearDoesntMatch()
    {
        $this->setExpectedException(Movie\NotFoundException::class);
        $results = $this->apiResults();
        $results[0]['original_name'] = $results[0]['original_title'] = 'New Ghostbuters';

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValue(['results' => $results]));

        $movie = $this->repository->tryFuzzyOnFail()
                    ->setThreshold(5)
                    ->oneOfTitle('Ghostbusters', 2016);
    }

    public function testOneOfTitleMatchesOnFuzzySearchIfYearMatches()
    {
        $results = $this->apiResults();
        $results[0]['original_name'] = $results[0]['original_title'] = 'New Ghostbuters';

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValue(['results' => $results]));

        $movie = $this->repository->tryFuzzyOnFail()
                    ->setThreshold(5)
                    ->oneOfTitle('Ghostbusters', 1989);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
        $this->assertEquals('Ghostbusters II', $movie->provideMovieInterest()['title']);
    }

    public function testOneOfTitleThrowsExceptionIfThresholdIsTooLow()
    {
        $this->setExpectedException(Movie\NotFoundException::class);
        $results = $this->apiResults();
        $results[0]['original_name'] = $results[0]['original_title'] = 'New Ghostbuters';

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValue(['results' => $results]));

        $movie = $this->repository->tryFuzzyOnFail()
                    ->setThreshold(0)
                    ->oneOfTitle('Ghostbusters', 2016);
    }

    public function testWithAlternateTitlesReturnsRepositoryObject()
    {
        $repository = $this->repository->withAlternateTitles();
        $this->assertNotNull($repository);
        $this->assertInstanceOf(Movie\MovieRepository::class, $repository);
    }

    public function testWithAlternateTitlesWithNoAlternateTitlesReturnsSameMovieObject()
    {
        $this->adapter->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(['movie/1'], ['movie/1'], ['movie/1/alternate_titles'])
            ->will($this->onConsecutiveCalls($this->apiResults()[0], $this->apiResults()[0], ['titles' => []]));

        $movie = $this->repository->oneOfId(1);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);

        $expected = $movie->provideMovieInterest();

        $movie = $this->repository->withAlternateTitles()
                    ->oneOfId(1);

        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testWithAlternateTitlesAddsNewAlternateTitles()
    {
        $alternateTitles = [
            'titles' => [
                ['title' => 'Los Ghostbusters'],
                ['title' => 'Les Ghostbusters'],
            ],
        ];

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['movie/1'], ['movie/1/alternate_titles'])
            ->will($this->onConsecutiveCalls($this->apiResults()[0], $alternateTitles));

        $movie = $this->repository->withAlternateTitles()
                    ->oneOfId(1);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
        $this->assertNotEquals([], $movie->provideMovieInterest()['alternateTitles']);
    }

    public function testWithCastReturnsRepositoryObject()
    {
        $repository = $this->repository->withCast();
        $this->assertNotNull($repository);
        $this->assertInstanceOf(Movie\MovieRepository::class, $repository);
    }

    public function testWithCastWithNoCastReturnsSameMovieObject()
    {
        $this->adapter->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(['movie/1'], ['movie/1'], ['movie/1/credits'])
            ->will($this->onConsecutiveCalls($this->apiResults()[0], $this->apiResults()[0], []));

        $movie = $this->repository->oneOfId(1);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);

        $expected = $movie->provideMovieInterest();

        $movie = $this->repository->withCast()
                    ->oneOfId(1);

        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testWithCastAddsNewCast()
    {
        $cast = [
            'cast' => [
                ['name' => 'Bill Murray', 'character' => 'Peter Venkman'],
                ['name' => 'Dan Akroyd', 'character' => 'Raymond Stantz'],
            ],
            'crew' => [
                ['name' => 'Ivan Reitman', 'job' => 'director', 'department' => 'Director'],
                ['name' => 'Harold Ramis', 'job' => 'writer', 'department' => 'Writers'],
            ],
        ];

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['movie/1'], ['movie/1/credits'])
            ->will($this->onConsecutiveCalls($this->apiResults()[0], $cast));

        $movie = $this->repository->withCast()
                    ->oneOfId(1);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
        $this->assertNotEquals([], $movie->provideMovieInterest()['cast']);
    }

    public function testWithKeywordsReturnsRepositoryObject()
    {
        $repository = $this->repository->withKeywords();
        $this->assertNotNull($repository);
        $this->assertInstanceOf(Movie\MovieRepository::class, $repository);
    }

    public function testWithKeywordsWithNoKeywordsReturnsSameMovieObject()
    {
        $this->adapter->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(['movie/1'], ['movie/1'], ['movie/1/keywords'])
            ->will($this->onConsecutiveCalls($this->apiResults()[0], $this->apiResults()[0], []));

        $movie = $this->repository->oneOfId(1);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);

        $expected = $movie->provideMovieInterest();

        $movie = $this->repository->withKeywords()
                    ->oneOfId(1);

        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testWithKeywordsAddsNewKeywords()
    {
        $keywords = [
            'keywords' => [
                ['name' => 'ghost'],
                ['name' => 'busting'],
            ],
        ];

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['movie/1'], ['movie/1/keywords'])
            ->will($this->onConsecutiveCalls($this->apiResults()[0], $keywords));

        $movie = $this->repository->withKeywords()
                    ->oneOfId(1);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
        $this->assertNotEquals([], $movie->provideMovieInterest()['keywords']);
    }

    public function testWithRecommendationsReturnsRepositoryObject()
    {
        $repository = $this->repository->withRecommendations();
        $this->assertNotNull($repository);
        $this->assertInstanceOf(Movie\MovieRepository::class, $repository);
    }

    public function testWithRecommendationsWithNoRecommendationsReturnsSameMovieObject()
    {
        $this->adapter->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(['movie/1'], ['movie/1'], ['movie/1/recommendations'])
            ->will($this->onConsecutiveCalls($this->apiResults()[0], $this->apiResults()[0], []));

        $movie = $this->repository->oneOfId(1);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);

        $expected = $movie->provideMovieInterest();

        $movie = $this->repository->withRecommendations()
                    ->oneOfId(1);

        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testWithRecommendationsAddsNewRecommendations()
    {
        $recommendations = [
            'results' => $this->apiResults(),
        ];

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['movie/1'], ['movie/1/recommendations'])
            ->will($this->onConsecutiveCalls($this->apiResults()[0], $recommendations));

        $movie = $this->repository->withRecommendations()
                    ->oneOfId(1);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
        $this->assertNotEquals([], $movie->provideMovieInterest()['recommendations']);
    }

    public function testWithReviewsReturnsRepositoryObject()
    {
        $repository = $this->repository->withReviews();
        $this->assertNotNull($repository);
        $this->assertInstanceOf(Movie\MovieRepository::class, $repository);
    }

    public function testWithReviewsWithNoReviewsReturnsSameMovieObject()
    {
        $this->adapter->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(['movie/1'], ['movie/1'], ['movie/1/reviews'])
            ->will($this->onConsecutiveCalls($this->apiResults()[0], $this->apiResults()[0], []));

        $movie = $this->repository->oneOfId(1);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);

        $expected = $movie->provideMovieInterest();

        $movie = $this->repository->withReviews()
                    ->oneOfId(1);

        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testWithReviewsAddsNewReviews()
    {
        $reviews = [
            'results' => [
                ['content' => 'It was good', 'author' => 'me', 'url' => 'www.imright.com'],
                ['content' => 'It sucked', 'author' => 'idiot', 'url' => 'www.moron.com'],
            ],
        ];

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['movie/1'], ['movie/1/reviews'])
            ->will($this->onConsecutiveCalls($this->apiResults()[0], $reviews));

        $movie = $this->repository->withReviews()
                    ->oneOfId(1);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
        $this->assertNotEquals([], $movie->provideMovieInterest()['reviews']);
    }

    public function testWithSimilarMoviesReturnsRepositoryObject()
    {
        $repository = $this->repository->withSimilarMovies();
        $this->assertNotNull($repository);
        $this->assertInstanceOf(Movie\MovieRepository::class, $repository);
    }

    public function testWithSimilarMoviesWithNoSimilarMoviesReturnsSameMovieObject()
    {
        $this->adapter->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(['movie/1'], ['movie/1'], ['movie/1/similar'])
            ->will($this->onConsecutiveCalls($this->apiResults()[0], $this->apiResults()[0], []));

        $movie = $this->repository->oneOfId(1);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);

        $expected = $movie->provideMovieInterest();

        $movie = $this->repository->withSimilarMovies()
                    ->oneOfId(1);

        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testWithSimilarMoviesAddsNewSimilarMovies()
    {
        $similarMovies = [
            'results' => $this->apiResults(),
        ];

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['movie/1'], ['movie/1/similar'])
            ->will($this->onConsecutiveCalls($this->apiResults()[0], $similarMovies));

        $movie = $this->repository->withSimilarMovies()
                    ->oneOfId(1);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
        $this->assertNotEquals([], $movie->provideMovieInterest()['similarMovies']);
    }

    public function testWithAllDataSearchesAllTheThings()
    {
        $this->adapter->expects($this->exactly(7))
            ->method('get')
            ->withConsecutive(
                ['movie/1'],
                ['movie/1/alternate_titles'],
                ['movie/1/credits'],
                ['movie/1/keywords'],
                ['movie/1/recommendations'],
                ['movie/1/reviews'],
                ['movie/1/similar']
            )
            ->will($this->onConsecutiveCalls($this->apiResults()[0], [], [], [], [], [], []));

        $movie = $this->repository->withAllData()
                    ->oneOfId(1);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
    }

    protected function apiResults()
    {
        return [
            [
                'release_date' => '1984-07-04',
                'first_air_date' => '1984-07-04',
                'id' => 1,
                'original_name' => 'Ghostbusters',
                'original_title' => 'Ghostbusters',
                'overview' => 'I aint afraid of no ghosts',
            ],
            [
                'release_date' => '1989-07-04',
                'first_air_date' => '1989-07-04',
                'id' => 2,
                'original_name' => 'Ghostbusters II',
                'original_title' => 'Ghostbusters II',
                'overview' => 'I still aint afraid of no ghosts',
            ],
        ];
    }
}
