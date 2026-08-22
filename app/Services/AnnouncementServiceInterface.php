<?php

namespace App\Services;

use App\Models\Announcement;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of AnnouncementServiceInterface
 */
interface AnnouncementServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getAll(): LengthAwarePaginator;

    /**
     * @param array $data
     * @return Announcement
     */
    public function create(array $data): Announcement;

    /**
     * @param Announcement $announcement
     * @param array $data
     * @return Announcement
     */
    public function update(Announcement $announcement, array $data): Announcement;

    /**
     * @param Announcement $announcement
     * @return void
     */
    public function delete(Announcement $announcement): void;
}
