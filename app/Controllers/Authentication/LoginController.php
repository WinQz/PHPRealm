<?php

namespace App\Controllers\Authentication;

use App\Controllers\BaseController;

class LoginController extends BaseController
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
        return view('authentication/login');
    }

    public function authentication()
    {
        $rules = $this->getValidationRules();
        
        if (!$this->validate($rules)) {
            return redirect()->back()->with(
                'errors',
                $this->validator->getErrors()
            );
        }

        $username = $this->getSanitizedUsername();
        $password = $this->getSanitizedPassword();

        $user = $this->getUserByUsername($username);

        if (!$user) {
            return $this->redirectWithError('This account name does not exist.');
        }

        if (password_verify($password, $user->password)) {
            $this->setUserSession($user);
            return redirect()->to('/welcome')->with(
                'success',
                'Welcome Adventurer, <b>' . $username . '</b>!'
            );
        } else {
            return $this->redirectWithError('Username or Password incorrect.');
        }
    }

    private function getValidationRules(): array
    {
        return [
            'username'  => 'required|min_length[4]|max_length[20]',
            'password'  => 'required|min_length[6]'
        ];
    }

    private function getSanitizedUsername(): string
    {
        return $this->request->getVar('username');
    }

    private function getSanitizedPassword(): string
    {
        return $this->request->getVar('password');
    }

    private function getUserByUsername(string $username)
    {
        return $this->userModel->where('username', $username)->first();
    }

    private function setUserSession($user)
    {
        $this->session->set('user', $user);
    }

    private function redirectWithError(string $message)
    {
        return redirect()->back()->with('errors', lang($message));
    }

    public function logout()
    {
        $this->session->remove('user');
        return redirect()->to('/')->with(
            'success',
            'Logged Out'
        );
    }
}