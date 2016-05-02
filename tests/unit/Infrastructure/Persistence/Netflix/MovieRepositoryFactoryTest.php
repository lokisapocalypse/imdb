<?php

namespace Fusani\Movies\Infrastructure\Persistence\Netflix;

use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Infrastructure\Persistence\Netflix\MovieRepositoryFactory
 */
class MovieFactoryRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateRepository()
    {
        $factory = new MovieRepositoryFactory();
        $repository = $factory->createRepository();

        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);
    }
}
