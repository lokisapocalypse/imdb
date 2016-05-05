<?php

namespace Fusani\Movies\Domain\Model\Movie;

class Source
{
    protected $details;
    protected $link;
    protected $name;
    protected $type;

    public function __construct($type, $name, $link, array $details = [])
    {
        $this->details = $details;
        $this->link = $link;
        $this->name = $name;
        $this->type = $type;
    }

    public function provideSourceInterest()
    {
        return [
            'details' => $this->details,
            'link' => $this->link,
            'name' => $this->name,
            'type' => $this->type,
        ];
    }
}
