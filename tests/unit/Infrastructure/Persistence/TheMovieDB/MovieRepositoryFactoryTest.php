<?php

namespace Fusani\Movies\Infrastructure\Persistence\TheMovieDB;

use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Infrastructure\Persistence\TheMovieDB\MovieRepositoryFactory
 */
class MovieFactoryRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateRepository()
    {
        $factory = new MovieRepositoryFactory();
        $repository = $factory->createRepository('apikey');

        $this->assertNotNull($repository);
        $this->assertInstanceOf(MovieRepository::class, $repository);
    }
}
