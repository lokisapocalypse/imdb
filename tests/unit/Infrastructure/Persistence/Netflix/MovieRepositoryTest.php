<?php

namespace Fusani\Movies\Infrastructure\Persistence\Netflix;

use Fusani\Movies\Domain\Model\Movie;
use Fusani\Movies\Infrastructure\Adapter;
use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Infrastructure\Persistence\Netflix\MovieRepository
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

    public function testDoNotTryFuzzyOnFailIsntImplemented()
    {
        $this->setExpectedException(NotYetImplementedException::class);
        $this->repository->doNotTryFuzzyOnFail();
    }

    public function testManyIsntImplemented()
    {
        $this->setExpectedException(NotYetImplementedException::class);
        $this->repository->many(0, 250, 'movie');
    }

    public function testManyEpisodesOfShowIsntImplemented()
    {
        $this->setExpectedException(NotYetImplementedException::class);
        $this->repository->manyEpisodesOfShow(new Movie\Movie(15, 'Guardians of the Galaxy', 'movie', 2014), 15);
    }

    public function testManyMoviesWithChanges()
    {
        $this->setExpectedException(NotYetImplementedException::class);
        $this->repository->manyMoviesWithChanges(10);
    }

    public function testManyWithTitleThrowsException()
    {
        $this->setExpectedException(NotYetImplementedException::class);
        $response = $this->repository->manyWithTitle('Guardians');
    }

    public function testManyWithTitleLikeThrowsException()
    {
        $this->setExpectedException(NotYetImplementedException::class);
        $response = $this->repository->manyWithTitleLike('Guardians');
    }

    public function testOneOfIdThrowsException()
    {
        $this->setExpectedException(NotYetImplementedException::class);
        $this->repository->oneOfId(1234);
    }

    public function testOneOfTitleWithNoMatch()
    {
        $this->setExpectedException(Movie\NotFoundException::class);

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['errorcode' => 404]));
        $this->repository->oneOfTitle(1234);
    }

    public function testOneOfTitleWithMatch()
    {
        $response = [
            'show_title' => 'Guardians of the Galaxy',
            'summary' => 'Superheros save the world',
            'poster' => 'www.mymovieposter.com',
            'mediatype' => 0,
            'release_year' => 2014,
            'show_id' => 15,
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
            'show_title' => 'Guardians of the Galaxy',
            'summary' => 'Superheros save the world',
            'poster' => 'www.mymovieposter.com',
            'mediatype' => 0,
            'release_year' => 2014,
            'show_id' => 15,
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($response));

        $movie = $this->repository->oneOfTitle(15, 2012);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::Class, $movie);
    }

    public function testSearchingForMovies()
    {
        $repository = $this->repository->searchForMovies();
        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);
        $this->assertEquals($repository, $this->repository);
    }

    public function testSearchingForShowsIsntImplemented()
    {
        $this->setExpectedException(NotYetImplementedException::class);
        $this->repository->searchForShows();
    }

    public function testSetThresholdIsntImplemented()
    {
        $this->setExpectedException(NotYetImplementedException::class);
        $this->repository->setThreshold(5);
    }

    public function testTryFuzzyOnFailIsntImplemented()
    {
        $this->setExpectedException(NotYetImplementedException::class);
        $this->repository->tryFuzzyOnFail();
    }

    public function testChainingWithMethods()
    {
        $repository = $this->repository->withAlternateTitles();

        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);

        $repository = $this->repository->withAllData();

        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);

        $repository = $this->repository->withCast();

        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);

        $repository = $this->repository->withKeywords();

        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);

        $repository = $this->repository->withNewEpisodes();

        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);

        $repository = $this->repository->withNewMovies();

        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);

        $repository = $this->repository->withRecommendations();

        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);

        $repository = $this->repository->withReviews();

        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);

        $repository = $this->repository->withSimilarMovies();

        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);

        $repository = $this->repository->withUpdatedEpisodes();

        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);

        $repository = $this->repository->withUpdatedMovies();

        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);
    }
}
