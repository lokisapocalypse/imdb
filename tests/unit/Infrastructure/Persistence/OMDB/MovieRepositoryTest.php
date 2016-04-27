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
        $this->setExpectedException(NotFoundException::class);

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
        $this->setExpectedException(NotFoundException::class);

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
}
