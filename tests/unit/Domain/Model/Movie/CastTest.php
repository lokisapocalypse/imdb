<?php

namespace Fusani\Movies\Domain\Model\Movie;

use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Domain\Model\Movie\Cast
 */
class CastBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testProvideCastInterest()
    {
        $cast = new Cast('Bill Murray', 'Peter Venkman');
        $expected = [
            'actor' => 'Bill Murray',
            'character' => 'Peter Venkman',
        ];

        $this->assertEquals($expected, $cast->provideCastInterest());
    }
}
