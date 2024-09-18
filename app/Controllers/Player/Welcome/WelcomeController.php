<?php

namespace App\Controllers\Player\Welcome;

use App\Controllers\BaseController;

class WelcomeController extends BaseController
{
    public function index(): string
    {
        return view('player/welcome');
    }
}
