<?php

namespace Fusani\Movies\Domain\Model\Movie;

class Cast
{
    protected $actor;
    protected $character;

    public function __construct($actor, $character)
    {
        $this->actor = $actor;
        $this->character = $character;
    }

    public function provideCastInterest()
    {
        return [
            'actor' => $this->actor,
            'character' => $this->character,
        ];
    }
}
