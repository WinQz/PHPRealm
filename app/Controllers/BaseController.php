<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\IncomingRequest;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $request;
    protected $session;
    protected $renderer;

    protected $helpers = [];

    public function __construct() {
        $this->userModel = model("UserModel");
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->session = \Config\Services::session();

        $this->renderer = service('renderer');

        if ($this->session->has('user')) {
            $this->setUserInView();
        }
    }

    /**
     * Fetch the user from the session and set it in the view.
     */
    protected function setUserInView(): void
    {
        $userId = $this->session->get('user')->id;
        $user = $this->userModel->find($userId);

        if ($user) {
            $this->renderer->setVar('user', $user);
        }
    }
}