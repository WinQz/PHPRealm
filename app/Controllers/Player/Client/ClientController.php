<?php

namespace App\Controllers\Player\Client;

use App\Controllers\BaseController;

class ClientController extends BaseController
{
    public function index(): string
    {
        return view('player/client');
    }

    public function getUserData() {
        if (session()->has('user')) {
            $userData = session()->get('user');
            return $this->response->setJSON($userData);
        } else {
            return $this->response->setJSON(['error' => 'User not logged in']);
        }
    }
}