<?php

namespace Fusani\Movies\Infrastructure\Persistence\Guidebox;

use Fusani\Movies\Domain\Model\Movie;
use Fusani\Movies\Infrastructure\Adapter;
use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Infrastructure\Persistence\Guidebox\MovieRepository
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

        $this->repository = new MovieRepository($this->adapter);
    }

    public function testDoNotTryFuzzyOnFail()
    {
        $this->repository->tryFuzzyOnFail()
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

    public function testManyEpisodesOfShowNoMatches()
    {
        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['results' => []]));

        $movie = new Movie\Movie(15, 'Guardians of the Galaxy', 'show', 2014);
        $movie = $this->repository->manyEpisodesOfShow($movie, 15);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals([], $interest['episodes']);
    }

    public function testManyEpisodesOfShowWithMatches()
    {
        $movieData = [
            'results' => [
                [
                    'id' => 15,
                    'title' => 'Guardians of the Galaxy',
                    'first_aired' => '2014-05-28',
                    'thumbnail_208x117' => 'www.movieposters.com',
                    'season_number' => 1,
                    'episode_number' => 1,
                    'overview' => 'Superheros save the day',
                ],
                [
                    'id' => 16,
                    'title' => 'Guardians of the Galaxy II',
                    'first_aired' => '2018-08-21',
                    'thumbnail_208x117' => 'www.movieposters.com',
                    'season_number' => 1,
                    'episode_number' => 2,
                    'overview' => 'Superheros save the day again',
                ],
            ],
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($movieData));

        $movie = new Movie\Movie(15, 'Guardians of the Galaxy', 'show', 2014);
        $movie = $this->repository->manyEpisodesOfShow($movie, 15);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals(2, count($interest['episodes']));
    }

    public function testManyWithTitleNoMatches()
    {
        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['results' => []]));

        $movies = $this->repository->manyWithTitle('Guardians');

        $this->assertEquals([], $movies);
    }

    public function testManyWithTitleMatches()
    {
        $movieData = [
            'results' => [
                [
                    'id' => 15,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2014,
                    'poster_120x171' => 'www.movieposters.com',
                ],
                [
                    'id' => 16,
                    'title' => 'Guardians of the Galaxy II',
                    'release_year' => 2018,
                    'poster_120x171' => 'www.movieposters.com',
                ],
            ],
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($movieData));

        $movies = $this->repository->manyWithTitle('Guardians');

        $this->assertNotEquals([], $movies);

        foreach ($movies as $movie) {
            $this->assertNotNull($movie);
            $this->assertInstanceOf(Movie\Movie::class, $movie);
        }
    }

    public function testManyWithTitleLikeNoMatches()
    {
        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['results' => []]));

        $movies = $this->repository->manyWithTitleLike('Guardians');

        $this->assertEquals([], $movies);
    }

    public function testManyWithTitleLikeWithMatches()
    {
        $movieData = [
            'results' => [
                [
                    'id' => 15,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2014,
                    'poster_120x171' => 'www.movieposters.com',
                ],
                [
                    'id' => 16,
                    'title' => 'Guardians of the Galaxy II',
                    'release_year' => 2018,
                    'poster_120x171' => 'www.movieposters.com',
                ],
            ],
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($movieData));

        $movies = $this->repository->manyWithTitleLike('Guardians');

        $this->assertNotEquals([], $movies);

        foreach ($movies as $movie) {
            $this->assertNotNull($movie);
            $this->assertInstanceOf(Movie\Movie::class, $movie);
        }
    }

    public function testOneOfIdNoMovieFound()
    {
        $this->setExpectedException(Movie\NotFoundException::class);
        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue([]));

        $this->repository->oneOfId(15);
    }

    public function testOneOfIdMovieMatch()
    {
        $movieData = [
            'id' => 15,
            'title' => 'Guardians of the Galaxy',
            'release_year' => 2014,
            'poster_120x171' => 'www.movieposters.com',
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($movieData));

        $movie = $this->repository->oneOfId(15);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
    }

    public function testOneOfTitleNoYearWithResults()
    {
        $movieData = [
            'results' => [
                [
                    'id' => 15,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2014,
                    'poster_120x171' => 'www.movieposters.com',
                ],
                [
                    'id' => 16,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2014,
                    'poster_120x171' => 'www.movieposters.com',
                ],
            ],
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($movieData));

        $movie = $this->repository->oneOfTitle('Guardians of the Galaxy');

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
        $this->assertEquals(15, $movie->identity());
    }

    public function testOneOfTitleWithYear()
    {
        $movieData = [
            'results' => [
                [
                    'id' => 15,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2014,
                    'poster_120x171' => 'www.movieposters.com',
                ],
                [
                    'id' => 16,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2018,
                    'poster_120x171' => 'www.movieposters.com',
                ],
            ],
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($movieData));

        $movie = $this->repository->oneOfTitle('Guardians of the Galaxy', 2018);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
        $this->assertEquals(16, $movie->identity());
    }

    public function testOneOfTitleWithYearNoMatch()
    {
        $this->setExpectedException(Movie\NotFoundException::class);

        $movieData = [
            'results' => [
                [
                    'id' => 15,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2014,
                    'poster_120x171' => 'www.movieposters.com',
                ],
                [
                    'id' => 16,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2018,
                    'poster_120x171' => 'www.movieposters.com',
                ],
            ],
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($movieData));

        $movie = $this->repository->oneOfTitle('Guardians of the Galaxy', 2017);
    }

    public function testOneOfTitleWithNoMatch()
    {
        $this->setExpectedException(Movie\NotFoundException::class);

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['results' => []]));

        $movie = $this->repository->oneOfTitle('Guardians of the Galaxy');
    }

    public function testOneOfTitleWithYearNoMatchForTvShow()
    {
        $this->setExpectedException(Movie\NotFoundException::class);

        $movieData = [
            'results' => [
                [
                    'id' => 15,
                    'title' => 'Guardians of the Galaxy',
                    'first_aired' => '2014-01-01',
                    'poster_120x171' => 'www.movieposters.com',
                ],
                [
                    'id' => 16,
                    'title' => 'Guardians of the Galaxy',
                    'first_aired' => '2018-01-01',
                    'poster_120x171' => 'www.movieposters.com',
                ],
            ],
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($movieData));

        $movie = $this->repository->oneOfTitle('Guardians of the Galaxy', 2017);
    }

    public function testOneOfTitleWithYearMatchForTvShow()
    {
        $movieData = [
            'results' => [
                [
                    'id' => 15,
                    'title' => 'Guardians of the Galaxy',
                    'first_aired' => '2014-01-01',
                    'poster_120x171' => 'www.movieposters.com',
                ],
                [
                    'id' => 16,
                    'title' => 'Guardians of the Galaxy',
                    'first_aired' => '2018-01-01',
                    'poster_120x171' => 'www.movieposters.com',
                ],
            ],
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($movieData));

        $movie = $this->repository->oneOfTitle('Guardians of the Galaxy', 2018);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::class, $movie);
        $this->assertEquals(16, $movie->identity());
    }

    public function testOneOfTitleWithFuzzyMatchDoesNotPassThreshold()
    {
        $this->setExpectedException(Movie\NotFoundException::class);

        $this->repository->tryFuzzyOnFail();

        $movieData = [
            'results' => [
                [
                    'id' => 15,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2014,
                    'poster_120x171' => 'www.movieposters.com',
                ],
                [
                    'id' => 16,
                    'title' => 'Guardians of the Galaxy II',
                    'release_year' => 2018,
                    'poster_120x171' => 'www.movieposters.com',
                ],
            ],
        ];

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->will($this->onConsecutiveCalls(['results' => []], $movieData));

        $movie = $this->repository->oneOfTitle('Guardians of the Galaxy I', 2018);
    }

    public function testOneOfTitleWithFuzzyMatchPassesThresholdSet()
    {
        $this->repository->tryFuzzyOnFail()
            ->setThreshold(2);

        $movieData = [
            'results' => [
                [
                    'id' => 15,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2014,
                    'poster_120x171' => 'www.movieposters.com',
                ],
                [
                    'id' => 16,
                    'title' => 'Guardians of the Galaxy II',
                    'release_year' => 2018,
                    'poster_120x171' => 'www.movieposters.com',
                ],
            ],
        ];

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->will($this->onConsecutiveCalls(['results' => []], $movieData));


        $movie = $this->repository->oneOfTitle('Guardians of the Galaxy I', 2018);
        $this->assertInstanceOf(Movie\Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals('Guardians of the Galaxy II', $interest['title']);
    }

    public function testSearchForMovies()
    {
        $repository = $this->repository->searchForMovies();
        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);
        $this->assertEquals($repository, $this->repository);

        $movieData = [
            'results' => [
                [
                    'id' => 15,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2014,
                    'poster_120x171' => 'www.movieposters.com',
                ],
                [
                    'id' => 16,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2018,
                    'poster_120x171' => 'www.movieposters.com',
                ],
            ],
        ];

        $this->adapter->expects($this->any())
            ->method('get')
            ->with($this->stringContains('movie'))
            ->will($this->onConsecutiveCalls($movieData, $movieData, $movieData['results'][0], $movieData));

        $this->repository->manyWithTitle('ghost');
        $this->repository->manyWithTitleLike('ghost');
        $this->repository->oneOfId(15);
        $this->repository->oneOfTitle('ghost');
    }

    public function testSearchForShows()
    {
        $repository = $this->repository->searchForShows();
        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);
        $this->assertEquals($repository, $this->repository);

        $movieData = [
            'results' => [
                [
                    'id' => 15,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2014,
                    'poster_120x171' => 'www.movieposters.com',
                ],
                [
                    'id' => 16,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2018,
                    'poster_120x171' => 'www.movieposters.com',
                ],
            ],
        ];

        $this->adapter->expects($this->any())
            ->method('get')
            ->with(
                $this->callback(function ($url) {
                    return $url == 'search/title/ghost' || strpos($url, 'show') !== false || $url == 'search/title/ghost/exact';
                }))
            ->will($this->onConsecutiveCalls($movieData, $movieData, $movieData['results'][0], $movieData));

        $this->repository->manyWithTitle('ghost');
        $this->repository->manyWithTitleLike('ghost');
        $this->repository->oneOfId(15);
        $this->repository->oneOfTitle('ghost');
    }

    public function testSetThreshold()
    {
        $this->repository->tryFuzzyOnFail();

        $movieData = [
            'results' => [
                [
                    'id' => 15,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2014,
                    'poster_120x171' => 'www.movieposters.com',
                ],
                [
                    'id' => 16,
                    'title' => 'Guardians of the Galaxy II',
                    'release_year' => 2018,
                    'poster_120x171' => 'www.movieposters.com',
                ],
            ],
        ];

        $this->adapter->expects($this->exactly(4))
            ->method('get')
            ->will($this->onConsecutiveCalls(['results' => []], $movieData, ['results' => []], $movieData));

        try {
            $movie = $this->repository->oneOfTitle('Guardians of the Galaxy I', 2018);
            $this->fail('This should have thrown an exception');
        } catch (Movie\NotFoundException $e) {
            $this->repository->setThreshold(2);
            $movie = $this->repository->oneOfTitle('Guardians of the Galaxy I', 2018);
            $this->assertInstanceOf(Movie\Movie::class, $movie);

            $interest = $movie->provideMovieInterest();
            $this->assertEquals('Guardians of the Galaxy II', $interest['title']);
        }
    }

    public function testSetThresholdReturnsSelf()
    {
        $repository = $this->repository->setThreshold(5);
        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);
    }

    public function testTryFuzzyOnFail()
    {
        $this->repository->tryFuzzyOnFail()
            ->setThreshold(2);

        $movieData = [
            'results' => [
                [
                    'id' => 15,
                    'title' => 'Guardians of the Galaxy',
                    'release_year' => 2014,
                    'poster_120x171' => 'www.movieposters.com',
                ],
                [
                    'id' => 16,
                    'title' => 'Guardians of the Galaxy II',
                    'release_year' => 2018,
                    'poster_120x171' => 'www.movieposters.com',
                ],
            ],
        ];

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->will($this->onConsecutiveCalls(['results' => []], $movieData));

        $movie = $this->repository->oneOfTitle('Guardians of the Galaxy I');
        $this->assertInstanceOf(Movie\Movie::class, $movie);

        $interest = $movie->provideMovieInterest();
        $this->assertEquals('Guardians of the Galaxy II', $interest['title']);
    }

    public function testTryFuzzyOnFailReturnsSelf()
    {
        $repository = $this->repository->tryFuzzyOnFail();
        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);
    }
}
