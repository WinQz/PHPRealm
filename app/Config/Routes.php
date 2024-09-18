<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('/', ['filter' => 'PlayerFilter', 'namespace' => 'App\Controllers\Landing'], function($routes) {
    $routes->get('', 'LandingController::index');
});

$routes->group('auth', ['filter' => 'PlayerFilter', 'namespace' => 'App\Controllers\Authentication'], function($routes) {
    $routes->get('login', 'LoginController::index');
    $routes->get('register', 'RegisterController::index');
    $routes->post('login/submit', 'LoginController::authentication');
    $routes->post('register/submit', 'RegisterController::register');
});

$routes->group('', ['filter' => 'GuestFilter', 'namespace' => 'App\Controllers\Player'], function($routes) {
    $routes->get('/welcome', 'Welcome\WelcomeController::index');
    $routes->get('/client', 'Client\ClientController::index');
    $routes->get('/api/client/player/getUserData', 'Client\ClientController::getUserData');
});