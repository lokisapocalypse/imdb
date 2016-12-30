<?php

namespace Fusani\Movies\Domain\Model\Movie;

class Poster
{
    protected $link;
    protected $size;
    protected $type;

    public function __construct($link, $type, $size = '')
    {
        $this->link = $link;
        $this->size = $size;
        $this->type = $type;
    }

    public function identity()
    {
        return $this->link.$this->size.$this->type;
    }

    public function providePosterInterest()
    {
        return [
            'link' => $this->link,
            'size' => $this->size,
            'type' => $this->type,
        ];
    }
}
