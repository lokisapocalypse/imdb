<?php

namespace Fusani\Movies\Domain\Model\Movie;

use DateTime;
use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Domain\Model\Movie\EpisodeBuilder
 */
class EpisodeBuilderTest extends PHPUnit_Framework_TestCase
{
    protected $builder;

    public function setup()
    {
        $this->builder = new EpisodeBuilder();
    }

    public function testBuildWithGuideboxNoSources()
    {
        $data = $this->guideboxEpisode();

        $episode = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $episode->provideEpisodeInterest();
        $this->assertEquals($interest, $this->expected());
    }

    public function testBuildWithGuideboxHasFreeSources()
    {
        $data = array_merge($this->guideboxEpisode(), ['free_web_sources' => [['display_name' => 'Netflix', 'link' => 'www.netflix.com']]]);

        $episode = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $episode->provideEpisodeInterest();
        $this->assertNotEmpty($interest['sources']['free']);
    }

    public function testBuildWithGuideboxHasTvEverywhereSources()
    {
        $data = array_merge($this->guideboxEpisode(), ['tv_everywhere_web_sources' => [['display_name' => 'Netflix', 'link' => 'www.netflix.com']]]);

        $episode = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $episode->provideEpisodeInterest();
        $this->assertNotEmpty($interest['sources']['tvEverywhere']);
    }

    public function testBuildWithGuideboxHasSubscriptionSources()
    {
        $data = array_merge($this->guideboxEpisode(), ['subscription_web_sources' => [['display_name' => 'Netflix', 'link' => 'www.netflix.com']]]);

        $episode = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $episode->provideEpisodeInterest();
        $this->assertNotEmpty($interest['sources']['subscription']);
    }

    public function testBuildWithGuideboxHasPurchaseSources()
    {
        $data = array_merge($this->guideboxEpisode(), ['purchase_web_sources' => [['display_name' => 'Netflix', 'link' => 'www.netflix.com']]]);

        $episode = $this->builder->buildFromGuidebox($data);

        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $episode->provideEpisodeInterest();
        $this->assertNotEmpty($interest['sources']['purchase']);
    }

    protected function expected()
    {
        return [
            'id' => 15,
            'episode' => 1,
            'firstAired' => new DateTime('2014-05-28'),
            'plot' => 'Superheros save the day',
            'poster' => 'www.movieposters.com',
            'season' => 1,
            'sources' => [],
            'title' => 'Guardians of the Galaxy',
        ];
    }

    protected function guideboxEpisode()
    {
        return [
            'id' => 15,
            'title' => 'Guardians of the Galaxy',
            'season_number' => 1,
            'episode_number' => 1,
            'first_aired' => '2014-05-28',
            'thumbnail_208x117' => 'www.movieposters.com',
            'overview' => 'Superheros save the day',
        ];
    }
}
