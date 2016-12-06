<?php

namespace Fusani\Movies\Domain\Model\Movie;

use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Domain\Model\Movie\Crew
 */
class CrewBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testProvideCrewInterest()
    {
        $crew = new Crew('BJ Novak', 'Producer', 'production');
        $expected = [
            'department' => 'production',
            'job' => 'Producer',
            'name' => 'BJ Novak',
        ];

        $this->assertEquals($expected, $crew->provideCrewInterest());
    }
}
