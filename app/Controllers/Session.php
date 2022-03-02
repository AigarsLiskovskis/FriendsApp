<?php

namespace App\Controllers;

class Session
{
    public static function isAuthorized(): bool
    {
        return isset($_SESSION["userid"]);
    }

    public static function setUser(int $id, string $name, string $surname)
    {
        $_SESSION["userid"] = $id;
        $_SESSION["name"] = $name;
        $_SESSION["surname"] = $surname;
    }


}