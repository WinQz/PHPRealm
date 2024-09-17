<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Landing\LandingController::index');

$routes->group('auth', ['namespace' => 'App\Controllers\Authentication'], function($routes) {
    $routes->get('login', 'LoginController::index');
    $routes->get('register', 'RegisterController::index');
});