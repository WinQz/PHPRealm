<?php

namespace App\Controllers\Landing;

use App\Controllers\BaseController;

class LandingController extends BaseController
{
    public function index(): string
    {
        return view('landing/landing');
    }
}
