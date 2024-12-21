<?php

namespace App\Controllers\Player\Client;

use App\Controllers\BaseController;
use App\Models\UserModel;

class ClientController extends BaseController
{
    protected $userModel;
    protected $session;

    public function __construct() {
        $this->userModel = model('UserModel');
        $this->session = session();
    }

    public function index(): string
    {
        return view('player/client');
    }

    public function getUserData() {

        if ($this->session->has('user')) {
            $userId = $this->session->get('user')->id;

            return $this->response->setJSON(['id' => $userId]);
        } else {
            return $this->response->setJSON(['error' => 'User not logged in']);
        }
    }
}