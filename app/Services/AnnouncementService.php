<?php

namespace App\Services;

use App\Models\Announcement;
use App\Repositories\AnnouncementRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Summary of AnnouncementService
 */
readonly class AnnouncementService implements AnnouncementServiceInterface
{
    /**
     * @param AnnouncementRepositoryInterface $announcementRepository
     */
    public function __construct(
        private AnnouncementRepositoryInterface $announcementRepository,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getAll(): LengthAwarePaginator
    {
        return $this->announcementRepository->findAllWithPagination();
    }

    /**
     * @param array $data
     * @return Announcement
     */
    public function create(array $data): Announcement
    {
        $data['user_uuid'] = Auth::user()->uuid;

        return $this->announcementRepository->create($data);
    }

    /**
     * @param Announcement $announcement
     * @param array $data
     * @return Announcement
     */
    public function update(Announcement $announcement, array $data): Announcement
    {
        return $this->announcementRepository->update($announcement, $data);
    }

    /**
     * @param Announcement $announcement
     * @return void
     */
    public function delete(Announcement $announcement): void
    {
        $this->announcementRepository->delete($announcement);
    }
}
