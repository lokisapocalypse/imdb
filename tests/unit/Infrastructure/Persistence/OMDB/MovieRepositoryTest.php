<?php

namespace Fusani\Movies\Infrastructure\Persistence\OMDB;

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

    public function testOneOfId()
    {
        $this->setExpectedException(NotYetImplementedException::class);
        $this->repository->oneOfId(1234);
    }
}
