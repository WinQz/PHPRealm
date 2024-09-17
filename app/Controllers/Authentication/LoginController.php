<?php

namespace App\Controllers\Authentication;

use App\Controllers\BaseController;

class LoginController extends BaseController
{
    public function index(): string
    {
        return view('authentication/login');
    }
}
