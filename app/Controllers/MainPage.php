<?php

namespace App\Controllers;

use App\Views\View;

class MainPage
{
    public function main(): View
    {
        if (Session::isAuthorized()) {
            return new View('/main', [
                'authorized' => true,
                'userFirstName' => $_SESSION['name']
            ]);
        } else {
            return new View('/main', []);
        }
    }
}