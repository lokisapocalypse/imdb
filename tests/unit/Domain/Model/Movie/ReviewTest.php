<?php

namespace Fusani\Movies\Domain\Model\Movie;

use PHPUnit_Framework_TestCase;

/**
 * @covers Fusani\Movies\Domain\Model\Movie\Review
 */
class ReviewBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testProvideReviewInterest()
    {
        $review = new Review('It was good', 'Joe', 'www.rottentomatoes.com');
        $expected = [
            'author' => 'Joe',
            'link' => 'www.rottentomatoes.com',
            'review' => 'It was good',
        ];

        $this->assertEquals($expected, $review->provideReviewInterest());
    }
}
