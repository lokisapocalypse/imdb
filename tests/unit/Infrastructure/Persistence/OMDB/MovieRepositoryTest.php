<?php

namespace Fusani\Movies\Infrastructure\Persistence\OMDB;

use Fusani\Movies\Domain\Model\Movie;
use Fusani\Movies\Infrastructure\Adapter;
use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Infrastructure\Persistence\OMDB\MovieRepository
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
            ->will($this->returnValue(['Response' => 'False']));

        $this->setExpectedException(Movie\NotFoundException::class);
        $movie = $this->repository->oneOfTitle('Guardians of the Galaxy I', 2018);
    }

    public function testDoNotTryFuzzyOnFailReturnsSelf()
    {
        $repository = $this->repository->doNotTryFuzzyOnFail();
        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);
    }

    public function testManyEpisodesOfShowIsntImplemented()
    {
        $this->setExpectedException(NotYetImplementedException::class);
        $this->repository->manyEpisodesOfShow(new Movie\Movie(15, 'Guardians of the Galaxy', 'movie', 2014), 15);
    }

    public function testManyWithTitleNoMatches()
    {
        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['Response' => 'False']));

        $response = $this->repository->manyWithTitle('Guardians');

        $this->assertEquals([], $response);
    }

    public function testManyWithTitleMatchesButDoesntMatchTitle()
    {
        $response = [
            'Response' => 'True',
            'Search' => [
                [
                    'Title' => 'Ghostbusters',
                    'Poster' => 'www.ghostbustersposter.com',
                    'Type' => 'movie',
                    'Year' => 1984,
                    'imdbID' => 15,
                ],
                [
                    'Title' => 'Ghost',
                    'Poster' => 'www.ghostposter.com',
                    'Type' => 'movie',
                    'Year' => 1990,
                    'imdbID' => 150,
                ],
            ],
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($response));

        $response = $this->repository->manyWithTitle('Guardians');

        $this->assertEquals([], $response);
    }

    public function testManyWithTitleMatchesWithActualMatches()
    {
        $response = [
            'Response' => 'True',
            'Search' => [
                [
                    'Title' => 'Ghostbusters',
                    'Poster' => 'www.ghostbustersposter.com',
                    'Type' => 'movie',
                    'Year' => 1984,
                    'imdbID' => 15,
                ],
                [
                    'Title' => 'Ghost',
                    'Poster' => 'www.ghostposter.com',
                    'Type' => 'movie',
                    'Year' => 1990,
                    'imdbID' => 150,
                ],
            ],
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($response));

        $response = $this->repository->manyWithTitle('Ghost');

        $this->assertNotEquals([], $response);
        $this->assertEquals(1, count($response));
        $this->assertInstanceOf(Movie\Movie::class, $response[0]);
    }

    public function testManyWithTitleLikeNoMatches()
    {
        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['Response' => 'False']));

        $response = $this->repository->manyWithTitleLike('Guardians');

        $this->assertEquals([], $response);
    }

    public function testManyWithTitleLikeWithMatches()
    {
        $response = [
            'Response' => 'True',
            'Search' => [
                [
                    'Title' => 'Ghostbusters',
                    'Poster' => 'www.ghostbustersposter.com',
                    'Type' => 'movie',
                    'Year' => 1984,
                    'imdbID' => 15,
                ],
                [
                    'Title' => 'Ghost',
                    'Poster' => 'www.ghostposter.com',
                    'Type' => 'movie',
                    'Year' => 1990,
                    'imdbID' => 150,
                ],
            ],
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($response));

        $movies = $this->repository->manyWithTitleLike('ghost');

        $this->assertTrue(is_array($movies));
        $this->assertEquals(2, count($movies));

        foreach ($movies as $movie) {
            $this->assertInstanceOf(Movie\Movie::class, $movie);
        }
    }

    public function testOneOfIdWithNoMatch()
    {
        $this->setExpectedException(Movie\NotFoundException::class);

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['Response' => 'False']));
        $this->repository->oneOfId(1234);
    }

    public function testOneOfIdWithMatch()
    {
        $response = [
            'Response' => 'True',
            'Title' => 'Guardians of the Galaxy',
            'Plot' => 'Superheros save the world',
            'Poster' => 'N/A',
            'Type' => 'movie',
            'Year' => 2014,
            'imdbID' => 15,
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($response));

        $movie = $this->repository->oneOfId(15);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::Class, $movie);
    }

    public function testOneOfTitleWithNoMatch()
    {
        $this->setExpectedException(Movie\NotFoundException::class);

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['Response' => 'False']));
        $this->repository->oneOfTitle(1234);
    }

    public function testOneOfTitleWithMatch()
    {
        $response = [
            'Response' => 'True',
            'Title' => 'Guardians of the Galaxy',
            'Plot' => 'Superheros save the world',
            'Poster' => 'N/A',
            'Type' => 'movie',
            'Year' => 2014,
            'imdbID' => 15,
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($response));

        $movie = $this->repository->oneOfTitle(15);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::Class, $movie);
    }

    public function testOneOfTitleWithYear()
    {
        $response = [
            'Response' => 'True',
            'Title' => 'Guardians of the Galaxy',
            'Plot' => 'Superheros save the world',
            'Poster' => 'my poster',
            'Type' => 'movie',
            'Year' => 2012,
            'imdbID' => 15,
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($response));

        $movie = $this->repository->oneOfTitle(15, 2012);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::Class, $movie);
    }

    public function testOneOfTitleWithFuzzyMatchDoesNotPassThreshold()
    {
        $this->setExpectedException(Movie\NotFoundException::class);

        $this->repository->tryFuzzyOnFail();

        $movieData = [
            'Response' => 'True',
            'Search' => [
                [
                    'Title' => 'Guardians of the Galaxy',
                    'Poster' => 'www.guardians.com',
                    'Type' => 'movie',
                    'Year' => 2014,
                    'imdbID' => 15,
                ],
                [
                    'Title' => 'Guardians of the Galaxy II',
                    'Poster' => 'www.guardians2.com',
                    'Type' => 'movie',
                    'Year' => 2018,
                    'imdbID' => 150,
                ],
            ],
        ];

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->will($this->onConsecutiveCalls(['Response' => 'False'], $movieData));

        $movie = $this->repository->oneOfTitle('Guardians of the Galaxy I', 2018);
    }

    public function testOneOfTitleWithFuzzyMatchPassesThresholdSet()
    {
        $this->repository->tryFuzzyOnFail()
            ->setThreshold(2);

        $movieData = [
            'Response' => 'True',
            'Search' => [
                [
                    'Title' => 'Guardians of the Galaxy',
                    'Poster' => 'www.guardians.com',
                    'Type' => 'movie',
                    'Year' => 2014,
                    'imdbID' => 15,
                ],
                [
                    'Title' => 'Guardians of the Galaxy II',
                    'Poster' => 'www.guardians2.com',
                    'Type' => 'movie',
                    'Year' => 2018,
                    'imdbID' => 150,
                ],
            ],
        ];

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->will($this->onConsecutiveCalls(['Response' => 'False'], $movieData));


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
            'Response' => true,
            'Search' => [
                [
                    'imdbID' => 150,
                    'Title' => 'Guardians of the Galaxy',
                    'Year' => 2014,
                    'Type' => 'movie',
                ],
                [
                    'imdbID' => 16,
                    'Title' => 'Guardians of the Galaxy II',
                    'Year' => 2018,
                    'Type' => 'movie',
                ],
            ],
        ];

        $oneMovie = array_merge(['Response' => 'true'], $movieData['Search'][0]);

        $this->adapter->expects($this->exactly(3))
            ->method('get')
            ->with(
                $this->equalTo(''),
                $this->callback(function ($param) {
                    return $param['type'] == 'movie';
                }))
            ->will($this->onConsecutiveCalls($movieData, $movieData, $oneMovie));

        $this->repository->manyWithTitle('ghost');
        $this->repository->manyWithTitleLike('ghost');
        $this->repository->oneOfTitle('ghost');
    }

    public function testSearchForShows()
    {
        $repository = $this->repository->searchForShows();
        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);
        $this->assertEquals($repository, $this->repository);

        $movieData = [
            'Response' => true,
            'Search' => [
                [
                    'imdbID' => 115,
                    'Title' => 'Guardians of the Galaxy',
                    'Year' => 2014,
                    'Type' => 'movie',
                ],
                [
                    'imdbID' => 16,
                    'Title' => 'Guardians of the Galaxy II',
                    'Year' => 2018,
                    'Type' => 'movie',
                ],
            ],
        ];

        $oneMovie = array_merge(['Response' => 'true'], $movieData['Search'][0]);

        $this->adapter->expects($this->exactly(3))
            ->method('get')
            ->with(
                $this->equalTo(''),
                $this->callback(function ($param) {
                    return $param['type'] == 'series';
                }))
            ->will($this->onConsecutiveCalls($movieData, $movieData, $oneMovie, $movieData));

        $this->repository->manyWithTitle('ghost');
        $this->repository->manyWithTitleLike('ghost');
        $this->repository->oneOfTitle('ghost');
    }

    public function testSetThreshold()
    {
        $this->repository->tryFuzzyOnFail();

        $movieData = [
            'Response' => 'true',
            'Search' => [
                [
                    'imdbID' => 115,
                    'Title' => 'Guardians of the Galaxy',
                    'Year' => 2014,
                    'Type' => 'movie',
                ],
                [
                    'imdbID' => 16,
                    'Title' => 'Guardians of the Galaxy II',
                    'Year' => 2018,
                    'Type' => 'movie',
                ],
            ],
        ];

        $this->adapter->expects($this->exactly(4))
            ->method('get')
            ->will($this->onConsecutiveCalls(['Response' => 'False'], $movieData, ['Response' => 'False'], $movieData));

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
            'Response' => 'true',
            'Search' => [
                [
                    'imdbID' => 115,
                    'Title' => 'Guardians of the Galaxy',
                    'Year' => 2014,
                    'Type' => 'movie',
                ],
                [
                    'imdbID' => 16,
                    'Title' => 'Guardians of the Galaxy II',
                    'Year' => 2018,
                    'Type' => 'movie',
                ],
            ],
        ];

        $this->adapter->expects($this->exactly(2))
            ->method('get')
            ->will($this->onConsecutiveCalls(['Response' => 'False'], $movieData));

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
