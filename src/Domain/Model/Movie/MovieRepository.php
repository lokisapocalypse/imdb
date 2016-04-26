<?php

namespace Fusani\Omdb\Domain\Model\Movie;

interface MovieRepository
{
    public function manyWithTitleLike($title);
    public function oneOfId($id);
}
