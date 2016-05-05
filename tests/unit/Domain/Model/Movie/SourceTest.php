<?php

namespace Fusani\Movies\Domain\Model\Movie;

use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Domain\Model\Movie\Source
 */
class SourceBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testProvideSourceInterest()
    {
        $source = new Source('free', 'Netflix', 'www.netflix.com');
        $expected = [
            'details' => [],
            'link' => 'www.netflix.com',
            'name' => 'Netflix',
            'type' => 'free',
        ];

        $this->assertEquals($expected, $source->provideSourceInterest());
    }
}
