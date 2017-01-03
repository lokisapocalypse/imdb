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

    public function testBuildWithEpisodeInterestRendersSameInterest()
    {
        $interest = [
            'id' => 15,
            'cast' => [
                ['actor' => 'Joe', 'character' => 'Fred'],
                ['actor' => 'Bob', 'character' => 'Soloman'],
            ],
            'crew' => [
                ['department' => 'writing', 'job' => 'writer', 'name' => 'Susie'],
                ['department' => 'directors', 'job' => 'director', 'name' => 'Elliot'],
            ],
            'episode' => 1,
            'firstAired' => new DateTime('2016-01-01'),
            'plot' => 'New Years party',
            'posters' => [[
                'link' => 'www.newyearsparty.com',
                'type' => 'poster',
                'width' => 117,
                'height' => 208,
            ]],
            'season' => 1,
            'sources' => [
                'Subscription' => [[
                    'details' => [],
                    'link' => 'www.netflix.com',
                    'name' => 'Netflix',
                    'type' => 'Subscription',
                ]],
                'Paid' => [[
                    'details' => ['price' => '9.99'],
                    'link' => 'www.amazon.com',
                    'name' => 'Amazon',
                    'type' => 'Paid',
                ]],
            ],
            'title' => 'Its party time',
        ];

        $episode = $this->builder->buildFromEpisodeInterest($interest);

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);
        $this->assertEquals($interest, $episode->provideEpisodeInterest());
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

    public function testBuildFromTheMovieDBBasicInformation()
    {
        $episode = $this->builder->buildFromTheMovieDB($this->theMovieDBEpisode());

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $expected = array_merge($this->expected(), ['posters' => [], 'firstAired' => '2014-05-28']);

        $this->assertEquals($expected, $episode->provideEpisodeInterest());
    }

    public function testBuildFromTheMovieDBWithCrew()
    {
        $data = $this->theMovieDBEpisode();
        $data['crew'] = [
            ['name' => 'Harold Ramis', 'job' => 'Writer', 'department' => 'writing staff'],
            ['name' => 'Ivan Reitman', 'job' => 'Director', 'department' => 'director'],
        ];

        $episode = $this->builder->buildFromTheMovieDB($data);

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $episode->provideEpisodeInterest();

        $this->assertNotEquals([], $interest['crew']);
        $this->assertNotEmpty($interest['crew']);
    }

    public function testBuildFromTheMovieDBWithCast()
    {
        $data = $this->theMovieDBEpisode();
        $data['guest_stars'] = [
            ['name' => 'Slavitza Jovan', 'character' => 'Gozer'],
            ['name' => 'David Margulies', 'character' => 'Mayor'],
        ];

        $episode = $this->builder->buildFromTheMovieDB($data);

        $this->assertNotNull($episode);
        $this->assertInstanceOf(Episode::class, $episode);

        $interest = $episode->provideEpisodeInterest();

        $this->assertNotEquals([], $interest['cast']);
        $this->assertNotEmpty($interest['cast']);
    }

    protected function expected()
    {
        return [
            'cast' => [],
            'crew' => [],
            'id' => 15,
            'episode' => 1,
            'firstAired' => new DateTime('2014-05-28'),
            'plot' => 'Superheroes save the day',
            'posters' => [[
                'link' => 'www.movieposters.com',
                'type' => 'poster',
                'width' => 117,
                'height' => 208,
            ]],
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
            'overview' => 'Superheroes save the day',
        ];
    }

    protected function theMovieDBEpisode()
    {
        return [
            'id' => 15,
            'name' => 'Guardians of the Galaxy',
            'air_date' => '2014-05-28',
            'season_number' => 1,
            'episode_number' => 1,
            'overview' => 'Superheroes save the day',
        ];
    }
}
