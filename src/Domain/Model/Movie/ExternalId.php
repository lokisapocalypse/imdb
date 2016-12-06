<?php

namespace Fusani\Movies\Domain\Model\Movie;

class ExternalId
{
    protected $externalId;
    protected $source;

    public function __construct($externalId, $source)
    {
        $this->externalId = $externalId;
        $this->source = $source;
    }

    public function provideExternalIdInterest()
    {
        return [
            'externalId' => $this->externalId,
            'source' => $this->source,
        ];
    }
}
