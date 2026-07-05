<?php

namespace App\Repositories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Model;

interface AnnouncementRepositoryInterface extends BasicRepositoryInterface
{
    public function findByUuid(string $uuid): ?Announcement;

    public function create(array $data): Announcement;

    public function update(Announcement|Model $model, array $data): Announcement;

    public function delete(Model|Announcement $model): bool;
}
