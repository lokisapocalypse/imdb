<?php

namespace Fusani\Movies\Domain\Model\Movie;

interface MovieRepository
{
    public function manyWithTitle($title);
    public function manyWithTitleLike($title);
    public function oneOfId($id);
    public function oneOfTitle($title, $year = null);
}
