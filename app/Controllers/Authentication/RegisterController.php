<?php

namespace App\Controllers\Authentication;

use App\Controllers\BaseController;

class RegisterController extends BaseController
{

    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = model('userModel');
        $this->session = \Config\Services::session();
    }

    public function index(): string
    {
        return view('/authentication/register');
    }

    public function register()
    {
        $rules = $this->getValidationRules();
        $data = $this->getRequestData();

        if (!$this->validate($rules)) {
            return redirect()->back()->with(
                'errors', 
                $this->validator->getErrors()
            );
        }

        $this->createUser($data);
        return redirect()->to('dashboard')->with(
            'success', 
            'Your Account Is Created'
        );
    }

    private function getValidationRules(): array
    {
        return [
            'username'              => 'required|min_length[4]|max_length[20]|is_unique[users.username]',
            'email'                 => 'required|valid_email|is_unique[users.mail]',
            'password'              => 'required|min_length[6]',
            'confirm_password'      => 'required|matches[password]'
        ];
    }

    private function getRequestData(): array
    {
        return [
            'username'      => $this->request->getVar('username'),
            'mail'          => $this->request->getVar('email'),
            'password'      => $this->request->getVar('password')
        ];
    }

    private function createUser(array $data)
    {
        $userId = $this->userModel->insert($data);
        $user = $this->userModel->find($userId);
        $this->session->set('user', $user);
    }
}