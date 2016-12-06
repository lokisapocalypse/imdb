<?php

namespace Fusani\Movies\Domain\Model\Movie;

use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Domain\Model\Movie\ExternalId
 */
class ExternalIdBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testProvideExternalIdInterest()
    {
        $externalId = new ExternalId('tt1209841', 'The Movie DB');
        $expected = [
            'externalId' => 'tt1209841',
            'source' => 'The Movie DB',
        ];

        $this->assertEquals($expected, $externalId->provideExternalIdInterest());
    }
}
