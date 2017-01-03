<?php

namespace Fusani\Movies\Domain\Model\Movie;

class Poster
{
    protected $link;
    protected $type;
    protected $width;
    protected $height;

    public function __construct($link, $type, $width = 0, $height = 0)
    {
        $this->height = $height;
        $this->link = $link;
        $this->type = $type;
        $this->width = $width;
    }

    public function identity()
    {
        return sprintf(
            '%s-%d-x-%d-%s',
            $this->link,
            $this->width,
            $this->height,
            $this->type
        );
    }

    public function providePosterInterest()
    {
        return [
            'height' => $this->height,
            'link' => $this->link,
            'type' => $this->type,
            'width' => $this->width,
        ];
    }
}
