<?php

namespace Fusani\Movies\Domain\Model\Movie;

class Review
{
    protected $author;
    protected $link;
    protected $review;

    public function __construct($review, $author, $link)
    {
        $this->author = $author;
        $this->link = $link;
        $this->review = $review;
    }

    public function provideReviewInterest()
    {
        return [
            'author' => $this->author,
            'link' => $this->link,
            'review' => $this->review,
        ];
    }
}
