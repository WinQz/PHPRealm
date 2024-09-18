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
            
            $userData = $this->userModel->find($userId);

            if ($userData) {
                $userData = (object) $userData;

                unset(
                    $userData->mail, 
                    $userData->password, 
                    $userData->account_created
                );

                return $this->response->setJSON($userData);
            } else {
                return $this->response->setJSON(['error' => 'User not found']);
            }
        } else {
            return $this->response->setJSON(['error' => 'User not logged in']);
        }
    }
}