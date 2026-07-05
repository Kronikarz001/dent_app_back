<?php

namespace App\Repositories;

use App\Models\Announcement;
use App\Search\AnnouncementSearch;
use App\Search\Search;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class AnnouncementRepository extends SearchableRepository implements AnnouncementRepositoryInterface
{
    protected string $modelClass = Announcement::class;

    public function __construct(
        private AnnouncementSearch $search
    ) {}

    public function findAllWithPagination(array $params = []): LengthAwarePaginator
    {
        return $this->search->search($params);
    }

    protected function getSearchModel(): Search
    {
        return $this->search;
    }

    public function findByUuid(string $uuid): ?Announcement
    {
        return Announcement::where('uuid', $uuid)->first();
    }

    public function create(array $data): Announcement
    {
        return Announcement::create($data);
    }

    public function update(Announcement|Model $model, array $data): Announcement
    {
        $model->update($data);

        return $model->fresh();
    }

    public function delete(Model|Announcement $model): bool
    {
        return $model->delete();
    }
}
