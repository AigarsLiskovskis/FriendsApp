<?php

namespace App\Controllers;

class Session
{
    public static function isAuthorized(): bool
    {
        return isset($_SESSION["userid"]);
    }
}