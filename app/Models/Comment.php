<?php

namespace App\Models;

class Comment
{
    private int $id;
    private string $comment;
    private int $articleId;
    private int $userId;
    private string $createdAt;
    private string $creatorName;

    public function __construct(int    $id,
                                string $comment,
                                int    $articleId,
                                int    $userId,
                                string $createdAt,
                                string $creatorName)
    {
        $this->id = $id;
        $this->comment = $comment;
        $this->articleId = $articleId;
        $this->userId = $userId;
        $this->createdAt = $createdAt;
        $this->creatorName = $creatorName;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @return int
     */
    public function getArticleId(): int
    {
        return $this->articleId;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getCreatorName(): string
    {
        return $this->creatorName;
    }
}
