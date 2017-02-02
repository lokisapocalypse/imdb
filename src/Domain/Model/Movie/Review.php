<?php

namespace Fusani\Movies\Domain\Model\Movie;

class Review
{
    protected $author;
    protected $link;
    protected $review;
    protected $title;

    public function __construct($review, $author, $link)
    {
        $this->author = $author;
        $this->link = $link;
        $this->review = $review;
    }

    public function generateTitle()
    {
        $firstPeriod = strpos($this->review, '.');
        $this->title = $firstPeriod === false ? substr($this->review, 0, 25) : substr($this->review, 0, $firstPeriod);
    }

    public function provideReviewInterest()
    {
        return [
            'author' => $this->author,
            'link' => $this->link,
            'review' => $this->review,
            'title' => $this->title,
        ];
    }
}
