<?php

namespace Fusani\Movies\Domain\Model\Movie;

use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Domain\Model\Movie\Poster
 */
class PosterBuilderTest extends PHPUnit_Framework_TestCase
{
    protected $poster;

    public function setup()
    {
        $this->poster = new Poster('www.ghostbusters.com', 'poster', 171, 280);
    }

    public function testIdentity()
    {
        $this->assertEquals(
            'www.ghostbusters.com-171-x-280-poster',
            $this->poster->identity()
        );
    }

    public function testProvidePosterInterest()
    {
        $expected = [
            'link' => 'www.ghostbusters.com',
            'type' => 'poster',
            'width' => 171,
            'height' => 280,
        ];

        $this->assertEquals($expected, $this->poster->providePosterInterest());
    }
}
