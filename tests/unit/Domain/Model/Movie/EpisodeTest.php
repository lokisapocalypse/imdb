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
            'posters' => [],
            'plot' => null,
            'crew' => [],
            'cast' => [],
        ];

        $this->episode = new Episode(15, 'Guardians of the Galaxy', '2014-05-28', 1, 1);
    }

    public function testAddCastNoExistingCast()
    {
        $interest = $this->episode->provideEpisodeInterest();
        $this->assertEquals([], $interest['cast']);

        $episode = $this->episode->addCast('Bill Murray', 'Peter Venkman');

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $episode->provideEpisodeInterest();
        $expected = [
            ['actor' => 'Bill Murray', 'character' => 'Peter Venkman'],
        ];

        $this->assertEquals($expected, $interest['cast']);
    }

    public function testAddCastWithExistingCastAndNewCastAdded()
    {
        $episode = $this->episode->addCast('Bill Murray', 'Peter Venkman');
        $episode = $this->episode->addCast('Dan Akroyd', 'Raymond Stantz');

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $episode->provideEpisodeInterest();
        $expected = [
            ['actor' => 'Bill Murray', 'character' => 'Peter Venkman'],
            ['actor' => 'Dan Akroyd', 'character' => 'Raymond Stantz'],
        ];

        $this->assertEquals($expected, $interest['cast']);
    }

    public function testAddDuplicateCast()
    {
        $episode = $this->episode->addCast('Bill Murray', 'Peter Venkman');
        $episode = $this->episode->addCast('Bill Murray', 'Peter Venkman');

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $episode->provideEpisodeInterest();
        $expected = [
            ['actor' => 'Bill Murray', 'character' => 'Peter Venkman'],
        ];

        $this->assertEquals($expected, $interest['cast']);
    }

    public function testAddCrewWithNoCrew()
    {
        $interest = $this->episode->provideEpisodeInterest();
        $this->assertEquals([], $interest['crew']);

        $episode = $this->episode->addCrew('Ivan Reitman', 'Director', 'directors');

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $episode->provideEpisodeInterest();
        $expected = [
            ['department' => 'directors', 'job' => 'Director', 'name' => 'Ivan Reitman'],
        ];

        $this->assertEquals($expected, $interest['crew']);
    }

    public function testAddNewCrewWithExistingCrew()
    {
        $episode = $this->episode->addCrew('Ivan Reitman', 'Director', 'directors');
        $episode = $this->episode->addCrew('Harold Ramis', 'Writer', 'writers');

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $episode->provideEpisodeInterest();
        $expected = [
            ['department' => 'directors', 'job' => 'Director', 'name' => 'Ivan Reitman'],
            ['department' => 'writers', 'job' => 'Writer', 'name' => 'Harold Ramis'],
        ];

        $this->assertEquals($expected, $interest['crew']);
    }

    public function testAddDuplicateCrew()
    {
        $episode = $this->episode->addCrew('Ivan Reitman', 'Director', 'directors');
        $episode = $this->episode->addCrew('Ivan Reitman', 'Director', 'directors');

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $episode->provideEpisodeInterest();
        $expected = [
            ['department' => 'directors', 'job' => 'Director', 'name' => 'Ivan Reitman'],
        ];

        $this->assertEquals($expected, $interest['crew']);
    }

    public function testAddPosterWithNoPosters()
    {
        $episode = $this->episode->addPoster('www.ghostbusters.com/poster', 'poster', 117, 208);
        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $episode = $this->episode->addPoster('www.ghostbusters.com/banner', 'banner', 380, 700);
        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $expected = [
            ['link' => 'www.ghostbusters.com/poster', 'type' => 'poster', 'width' => 117, 'height' => 208],
            ['link' => 'www.ghostbusters.com/banner', 'type' => 'banner', 'width' => 380, 'height' => 700],
        ];

        $this->assertEquals($expected, $episode->provideEpisodeInterest()['posters']);
    }

    public function testAddDuplicatePoster()
    {
        $episode = $this->episode->addPoster('www.ghostbusters.com/poster', 'poster', 117, 208);
        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $episode = $this->episode->addPoster('www.ghostbusters.com/poster', 'poster', 117, 208);
        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $expected = [
            ['link' => 'www.ghostbusters.com/poster', 'type' => 'poster', 'width' => 117, 'height' => 208],
        ];

        $this->assertEquals($expected, $episode->provideEpisodeInterest()['posters']);
    }

    public function testAddSourceWithNoSources()
    {
        $interest = $this->episode->provideEpisodeInterest();
        $this->assertEquals([], $interest['sources']);

        $episode = $this->episode->addSource('free', 'Netflix', 'www.netflix.com');

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $this->episode->provideEpisodeInterest();
        $expected = [
            'free' => [[
                'details' => [],
                'link' => 'www.netflix.com',
                'name' => 'Netflix',
                'type' => 'free',
            ]]
        ];
        $this->assertEquals($expected, $interest['sources']);
    }

    public function testAddSourceWithExistingSourcesAndNewSource()
    {
        $interest = $this->episode->provideEpisodeInterest();
        $this->assertEquals([], $interest['sources']);

        $episode = $this->episode->addSource('free', 'Netflix', 'www.netflix.com');

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $episode = $this->episode->addSource('purchase', 'Amazon', 'www.amazon.com');

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $this->episode->provideEpisodeInterest();

        $expected = [
            'free' => [[
                'details' => [],
                'link' => 'www.netflix.com',
                'name' => 'Netflix',
                'type' => 'free',
            ]],
            'purchase' => [[
                'details' => [],
                'link' => 'www.amazon.com',
                'name' => 'Amazon',
                'type' => 'purchase',
            ]],
        ];

        $this->assertEquals($expected, $interest['sources']);
    }

    public function testAddSourceWithDuplicateSources()
    {
        $interest = $this->episode->provideEpisodeInterest();
        $this->assertEquals([], $interest['sources']);

        $episode = $this->episode->addSource('free', 'Netflix', 'www.netflix.com');
        $episode = $this->episode->addSource('free', 'Netflix', 'www.netflix.com');

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $this->episode->provideEpisodeInterest();
        $expected = [
            'free' => [[
                'details' => [],
                'link' => 'www.netflix.com',
                'name' => 'Netflix',
                'type' => 'free',
            ]]
        ];
        $this->assertEquals($expected, $interest['sources']);
    }

    public function testAddSourceWithDifferentTypes()
    {
        $interest = $this->episode->provideEpisodeInterest();
        $this->assertEquals([], $interest['sources']);

        $episode = $this->episode->addSource('free', 'Netflix', 'www.netflix.com');
        $episode = $this->episode->addSource('subscription', 'Netflix', 'www.netflix.com');

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $this->episode->provideEpisodeInterest();
        $expected = [
            'free' => [[
                'details' => [],
                'link' => 'www.netflix.com',
                'name' => 'Netflix',
                'type' => 'free',
            ]],
            'subscription' => [[
                'details' => [],
                'link' => 'www.netflix.com',
                'name' => 'Netflix',
                'type' => 'subscription',
            ]],
        ];

        $this->assertEquals($expected, $interest['sources']);
    }

    public function testAddMultipleSourcesOfTheSameType()
    {
        $interest = $this->episode->provideEpisodeInterest();
        $this->assertEquals([], $interest['sources']);

        $episode = $this->episode->addSource('free', 'Netflix', 'www.netflix.com');
        $episode = $this->episode->addSource('free', 'Amazon', 'www.amazon.com');

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $this->episode->provideEpisodeInterest();
        $expected = [
            'free' => [
                [
                    'details' => [],
                    'link' => 'www.netflix.com',
                    'name' => 'Netflix',
                    'type' => 'free',
                ],
                [
                    'details' => [],
                    'link' => 'www.amazon.com',
                    'name' => 'Amazon',
                    'type' => 'free',
                ],
            ],
        ];

        $this->assertEquals($expected, $interest['sources']);
    }

    public function testIdentity()
    {
        $this->assertEquals('s01e01-15', $this->episode->identity());
    }

    public function testProvideInterestSimpleObject()
    {
        $this->assertEquals($this->expected, $this->episode->provideEpisodeInterest());
    }

    public function testSetPlot()
    {
        $this->episode->setPlot('Superheros save the world');
        $expected = array_merge($this->expected, ['plot' => 'Superheros save the world']);
        $this->assertEquals($expected, $this->episode->provideEpisodeInterest());
    }

    public function testTitle()
    {
        $this->assertEquals('Guardians of the Galaxy', $this->episode->title());
    }
}
