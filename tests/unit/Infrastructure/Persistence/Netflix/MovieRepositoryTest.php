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

    public function testManyWithTitleThrowsException()
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
}
