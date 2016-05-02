<?php

namespace Fusani\Movies\Infrastructure\Persistence\Netflix;

use Fusani\Movies\Domain\Model\Movie;
use Fusani\Movies\Infrastructure\Adapter;

/**
 * This is not the official Netflix API but uses the Netflix Roulette open API. If using this repository, please
 * credit the Netflix Roulette API.
 *
 * This API only supports online streaming movies.
 */
class MovieRepository implements Movie\MovieRepository
{
    protected $adapter;
    protected $movieBuilder;

    public function __construct(Adapter\Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->movieBuilder = new Movie\MovieBuilder();
    }

    public function manyWithTitleLike($title)
    {
        // unfortunately, this api does not support this kind of method
        throw new NotYetImplementedException();
    }

    public function oneOfId($id)
    {
        // unfortunately, this api does not support this kind of method
        throw new NotYetImplementedException();
    }

    public function oneOfTitle($title, $year = null)
    {
        $params = ['title' => $title];

        if ($year) {
            $params['year'] = $year;
        }

        $result = $this->adapter->get('', $params);

        if (!empty($result['errorcode']) && $result['errorcode'] == 404) {
            throw new NotFoundException('No movie was found.');
        }

        return $this->movieBuilder->buildFromNetflix($result);
    }
}
