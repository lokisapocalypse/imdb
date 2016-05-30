<?php

namespace Fusani\Movies\Domain\Model\Movie;

use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Domain\Model\Movie\Episode
 */
class EpisodeTest extends PHPUnit_Framework_TestCase
{
    protected $expected;
    protected $episode;

    public function setup()
    {
        $this->expected = [
            'id' => 15,
            'title' => 'Guardians of the Galaxy',
            'episode' => 1,
            'firstAired' => '2014-05-28',
            'season' => 1,
            'sources' => [],
            'poster' => null,
            'plot' => null,
        ];

        $this->episode = new Episode(15, 'Guardians of the Galaxy', '2014-05-28', 1, 1);
    }

    public function testAddSource()
    {
        $interest = $this->episode->provideepisodeInterest();
        $this->assertEquals([], $interest['sources']);

        $this->episode->addSource('free', 'Netflix', 'www.netflix.com');
        $this->episode->addSource('purchase', 'Amazon', 'www.amazon.com');

        $interest = $this->episode->provideepisodeInterest();

        $this->assertNotEquals([], $interest['sources']);
    }

    public function testIdentity()
    {
        $this->assertEquals(15, $this->episode->identity());
    }

    public function testProvideInterestSimpleObject()
    {
        $this->assertEquals($this->expected, $this->episode->provideepisodeInterest());
    }

    public function testSetPoster()
    {
        $this->episode->setPoster('www.episodeposters.com/guardians-of-the-galaxy');
        $expected = array_merge($this->expected, ['poster' => 'www.episodeposters.com/guardians-of-the-galaxy']);
        $this->assertEquals($expected, $this->episode->provideepisodeInterest());
    }

    public function testSetPlot()
    {
        $this->episode->setPlot('Superheros save the world');
        $expected = array_merge($this->expected, ['plot' => 'Superheros save the world']);
        $this->assertEquals($expected, $this->episode->provideepisodeInterest());
    }

    public function testTitle()
    {
        $this->assertEquals('Guardians of the Galaxy', $this->episode->title());
    }
}
