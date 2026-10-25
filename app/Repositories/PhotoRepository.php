<?php

namespace App\Repositories;

use App\Models\Photo;
use App\Search\PhotoSearch;
use App\Search\Search;

/**
 * Summary of PhotoRepository
 *
 * @extends SearchableRepository<Photo>
 */
class PhotoRepository extends SearchableRepository implements PhotoRepositoryInterface
{
    /**
     * @var class-string<Photo>
     */
    protected string $modelClass = Photo::class;

    /**
     * @param PhotoSearch $search
     */
    public function __construct(
        private readonly PhotoSearch $search
    ) {}

    /**
     * @return Search
     */
    protected function getSearchModel(): Search
    {
        return $this->search;
    }
}
