<?php

namespace App\Services;

use App\Models\Room;
use App\Repositories\RoomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of RoomService
 */
readonly class RoomService implements RoomServiceInterface
{
    /**
     * @param RoomRepositoryInterface $roomRepository
     */
    public function __construct(
        private RoomRepositoryInterface $roomRepository,
    ) {}

    /**
     * @return LengthAwarePaginator
     */
    public function getRooms(): LengthAwarePaginator
    {
        return $this->roomRepository->findAllWithPagination();
    }

    /**
     * @return Collection
     */
    public function getRoomsList(): Collection
    {
        return $this->roomRepository->findSelectAll(['uuid', 'name'], ['is_active' => true]);
    }

    /**
     * @param array $data
     * @return Room
     */
    public function createRoom(array $data): Room
    {
        return $this->roomRepository->create($data);
    }

    /**
     * @param Room $room
     * @param array $data
     * @return Room
     */
    public function updateRoom(Room $room, array $data): Room
    {
        return $this->roomRepository->update($room, $data);
    }

    /**
     * @param Room $room
     * @return void
     */
    public function deleteRoom(Room $room): void
    {
        $this->roomRepository->delete($room);
    }
}
