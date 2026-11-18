<?php

namespace App\Repositories;

use App\Models\Announcement;
use App\Search\AnnouncementSearch;
use App\Search\Search;

/**
 * Summary of AnnouncementRepository
 *
 * @extends SearchableRepository<Announcement>
 */
class AnnouncementRepository extends SearchableRepository implements AnnouncementRepositoryInterface
{
    /**
     * @var class-string<Announcement>
     */
    protected string $modelClass = Announcement::class;

    /**
     * @param AnnouncementSearch $search
     */
    public function __construct(
        private readonly AnnouncementSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }
}
