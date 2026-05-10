<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'User\UserController::login');


$routes->post('/login', 'User\UserController::doLogin');
$routes->get('/register', 'User\UserController::register');
$routes->post('/register', 'User\UserController::saveUser');
$routes->get('/register-sante', 'User\UserController::ViewSante');
$routes->post('/save-sante', 'User\UserController::saveSante');
$routes->post('/save-user', 'User\UserController::saveUser');
$routes->get('/logout', 'User\UserController::logout');
$routes->get('/profil', 'User\UserController::profil');
$routes->get('/Accueil', 'User\UserController::Accueil');
$routes->get('/objectif', 'User\UserController::getObjectif');
$routes->get('/objectifs/(:num)', 'User\UserController::ObjectifChoisie/$1');
$routes->post('/update-profil', 'User\UserController::updateProfil');
$routes->post('/update-account', 'User\UserController::updateAccount');

$routes->get('porte-monnaie', 'User\UserController::viewPorteMonnaie');
$routes->post('recharge-wallet', 'User\UserController::rechargeWallet');
$routes->get('gold', 'User\UserController::buyGold');
$routes->post('souscrire-regime', 'User\UserController::souscrireRegime');


$routes->get('/viewLoginAdmin', 'Admin\AdminController::viewLoginAdmin');
$routes->post('/doLoginAdmin', 'Admin\AdminController::doLoginAdmin');

$routes->group('admin', function($routes) {
    $routes->get('dashboard', 'Admin\AdminController::AdminAccueil');
    $routes->get('regimes', 'Admin\AdminController::adminRegimes');
    $routes->get('regimes/add', 'Admin\AdminController::addRegime');
    $routes->get('regimes/edit/(:num)', 'Admin\AdminController::editRegime/$1');
    $routes->post('regimes/save', 'Admin\AdminController::saveRegime');
    $routes->get('regimes/delete/(:num)', 'Admin\AdminController::deleteRegime/$1');

    $routes->get('activites', 'Admin\AdminController::adminActivites');
    $routes->get('activites/add', 'Admin\AdminController::addActivite');
    $routes->get('activites/edit/(:num)', 'Admin\AdminController::editActivite/$1');
    $routes->post('activites/save', 'Admin\AdminController::saveActivite');
    $routes->get('activites/delete/(:num)', 'Admin\AdminController::deleteActivite/$1');

    $routes->get('codes', 'Admin\AdminController::adminCodes');
    $routes->get('parametres', 'Admin\AdminController::adminParametres');
    $routes->get('logout', 'Admin\AdminController::adminLogout');
});
