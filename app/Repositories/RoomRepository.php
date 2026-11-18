<?php

namespace App\Repositories;

use App\Models\Room;
use App\Search\RoomSearch;
use App\Search\Search;

/**
 * Summary of RoomRepository
 *
 * @extends SearchableRepository<Room>
 */
class RoomRepository extends SearchableRepository implements RoomRepositoryInterface
{
    /**
     * @var class-string<Room>
     */
    protected string $modelClass = Room::class;

    /**
     * @param RoomSearch $search
     */
    public function __construct(
        private readonly RoomSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }
}
