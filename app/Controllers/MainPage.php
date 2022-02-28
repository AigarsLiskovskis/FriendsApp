<?php

namespace App\Controllers;

use App\Views\View;

class MainPage
{
    public function main(): View
    {
        if (Session::isAuthorized()) {
            return new View('/main', [
                'signUp' => '',
                'login' => '',
                'logout' => 'LOGOUT'
            ]);
        } else {
            return new View('/main', [
                'signUp' => 'SIGN UP',
                'login' => 'LOGIN',
                'logout' => ''
            ]);
        }
    }

}