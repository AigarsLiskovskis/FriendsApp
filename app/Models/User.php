<?php

namespace App\Models;

class User
{
    private int $id;
    private ?string $email;
    private ?string $createdAt;
    private string $name;
    private string $surname;
    private string $birthday;

    public function __construct(int     $id,
                                string  $name,
                                string  $surname,
                                string  $birthday,
                                ?string $email = null,
                                ?string $createdAt = null)
    {

        $this->id = $id;
        $this->email = $email;
        $this->createdAt = $createdAt;
        $this->name = $name;
        $this->surname = $surname;
        $this->birthday = $birthday;
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
    public function getEmail(): string
    {
        return $this->email;
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * @return string
     */
    public function getBirthday(): string
    {
        return $this->birthday;
    }
}