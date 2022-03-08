<?php

namespace App\Models;

class Friend
{
    private int $id;
    private int $userId;
    private int $friendId;

    public function __construct(int $id, int $userId, int $friendId)
    {

        $this->id = $id;
        $this->userId = $userId;
        $this->friendId = $friendId;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getFriendId(): int
    {
        return $this->friendId;
    }
}