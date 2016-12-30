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
        $this->poster = new Poster('www.ghostbusters.com', 'poster', '280x171');
    }

    public function testIdentity()
    {
        $this->assertEquals(
            'www.ghostbusters.com280x171poster',
            $this->poster->identity()
        );
    }

    public function testProvidePosterInterest()
    {
        $expected = [
            'link' => 'www.ghostbusters.com',
            'type' => 'poster',
            'size' => '280x171',
        ];

        $this->assertEquals($expected, $this->poster->providePosterInterest());
    }
}
