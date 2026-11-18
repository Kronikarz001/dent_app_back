<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Summary of RoomServiceInterface
 */
interface RoomServiceInterface
{
    /**
     * @return LengthAwarePaginator
     */
    public function getRooms(): LengthAwarePaginator;

    /**
     * @return Collection
     */
    public function getRoomsList(): Collection;

    /**
     * @param array $data
     * @return Room
     */
    public function createRoom(array $data): Room;

    /**
     * @param Room $room
     * @param array $data
     * @return Room
     */
    public function updateRoom(Room $room, array $data): Room;

    /**
     * @param Room $room
     * @return void
     */
    public function deleteRoom(Room $room): void;
}
