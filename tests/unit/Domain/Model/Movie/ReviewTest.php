<?php

namespace Fusani\Movies\Domain\Model\Movie;

use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Domain\Model\Movie\Review
 */
class ReviewBuilderTest extends PHPUnit_Framework_TestCase
{
    protected $review;

    public function setup()
    {
        $this->review = new Review('It was good', 'Joe', 'www.rottentomatoes.com');
    }

    public function testProvideReviewInterest()
    {
        $expected = [
            'author' => 'Joe',
            'link' => 'www.rottentomatoes.com',
            'review' => 'It was good',
            'title' => '',
        ];

        $this->assertEquals($expected, $this->review->provideReviewInterest());
    }

    public function testGenerateTitleWithNoPeriodIsFirstTwentyFiveCharacters()
    {
        $reviewText = 'This is my review of this movie it will have a lot of characters but no period so lets see what happens';
        $review = new Review($reviewText, 'Joe', 'rottentomatoes.com');
        $review->generateTitle();

        $this->assertEquals('This is my review of this', $review->provideReviewInterest()['title']);
    }

    public function testGenerateTitleWithPeriodEndsAtPeriod()
    {
        $reviewText = 'This is my review of this movie. It will have a lot of characters but and a period. Lets see what happens.';
        $review = new Review($reviewText, 'Joe', 'rottentomatoes.com');
        $review->generateTitle();

        $this->assertEquals('This is my review of this movie', $review->provideReviewInterest()['title']);
    }
}
