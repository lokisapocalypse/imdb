<?php

namespace Fusani\Movies\Domain\Model\Movie;

class Crew
{
    protected $department;
    protected $job;
    protected $name;

    public function __construct($name, $job, $department)
    {
        $this->department = $department;
        $this->job = $job;
        $this->name = $name;
    }

    public function provideCrewInterest()
    {
        return [
            'department' => $this->department,
            'job' => $this->job,
            'name' => $this->name,
        ];
    }
}
