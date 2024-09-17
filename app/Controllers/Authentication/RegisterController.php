<?php 

namespace App\Controllers\Authentication;

use App\Controllers\BaseController;

class RegisterController extends BaseController
{
    public function index()
    {
        return view('authentication/register');
    }
}