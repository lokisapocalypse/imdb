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

    public function testManyWithTitleLike()
    {
        $this->setExpectedException(NotYetImplementedException::class);
        $this->repository->manyWithTitleLike('Guardians');
    }

    public function testOneOfIdWithNoMatch()
    {
        $this->setExpectedException(NotFoundException::class);

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['Response' => 'False']));
        $this->repository->oneOfId(1234);
    }

    public function testOneOfIdWithNoPoster()
    {
        $response = [
            'Response' => 'True',
            'Title' => 'Guardians of the Galaxy',
            'Plot' => 'Superheros save the world',
            'Poster' => 'N/A',
            'Type' => 'movie',
            'Year' => 2012,
            'imdbID' => 15,
        ];

        $expected = [
            'id' => 15,
            'plot' => 'Superheros save the world',
            'poster' => null,
            'title' => 'Guardians of the Galaxy',
            'type' => 'movie',
            'year' => 2012,
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($response));

        $movie = $this->repository->oneOfId(15);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::Class, $movie);
        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testOneOfIdWithPoster()
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

        $expected = [
            'id' => 15,
            'plot' => 'Superheros save the world',
            'poster' => 'my poster',
            'title' => 'Guardians of the Galaxy',
            'type' => 'movie',
            'year' => 2012,
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($response));

        $movie = $this->repository->oneOfId(15);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::Class, $movie);
        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testOneOfTitleWithNoMatch()
    {
        $this->setExpectedException(NotFoundException::class);

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['Response' => 'False']));
        $this->repository->oneOfTitle(1234);
    }

    public function testOneOfTitleWithNoPoster()
    {
        $response = [
            'Response' => 'True',
            'Title' => 'Guardians of the Galaxy',
            'Plot' => 'Superheros save the world',
            'Poster' => 'N/A',
            'Type' => 'movie',
            'Year' => 2012,
            'imdbID' => 15,
        ];

        $expected = [
            'id' => 15,
            'plot' => 'Superheros save the world',
            'poster' => null,
            'title' => 'Guardians of the Galaxy',
            'type' => 'movie',
            'year' => 2012,
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($response));

        $movie = $this->repository->oneOfTitle(15);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::Class, $movie);
        $this->assertEquals($expected, $movie->provideMovieInterest());
    }

    public function testOneOfTitleWithPoster()
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

        $expected = [
            'id' => 15,
            'plot' => 'Superheros save the world',
            'poster' => 'my poster',
            'title' => 'Guardians of the Galaxy',
            'type' => 'movie',
            'year' => 2012,
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($response));

        $movie = $this->repository->oneOfTitle(15);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::Class, $movie);
        $this->assertEquals($expected, $movie->provideMovieInterest());
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

        $expected = [
            'id' => 15,
            'plot' => 'Superheros save the world',
            'poster' => 'my poster',
            'title' => 'Guardians of the Galaxy',
            'type' => 'movie',
            'year' => 2012,
        ];

        $this->adapter->expects($this->once())
            ->method('get')
            ->will($this->returnValue($response));

        $movie = $this->repository->oneOfTitle(15, 2012);

        $this->assertNotNull($movie);
        $this->assertInstanceOf(Movie\Movie::Class, $movie);
        $this->assertEquals($expected, $movie->provideMovieInterest());
    }
}
