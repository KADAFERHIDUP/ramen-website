<?php

use App\Filters\AdminAuth;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Halaman utama = website (installer menyalin frontend ke public/)
$routes->get('/', 'Home::index');

// ---------- API JSON untuk website ----------
$routes->group('api', ['namespace' => 'App\Controllers\Api', 'filter' => 'cors'], static function ($routes) {
    $routes->get('health', 'Health::index');
    $routes->get('menu', 'Menu::index');
    $routes->post('orders', 'Orders::create');
    $routes->post('reservations', 'Reservations::create');
    $routes->options('(:any)', static fn () => service('response')->setStatusCode(204));
});

// ---------- Login admin ----------
$routes->get('admin/login', 'Auth::login');
$routes->post('admin/login', 'Auth::attempt', ['filter' => 'csrf']);
$routes->get('admin/logout', 'Auth::logout');

// ---------- Panel admin (wajib login, semua POST dicek CSRF) ----------
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => AdminAuth::class], static function ($routes) {
    $routes->get('/', 'Dashboard::index');

    $routes->get('pesanan', 'Orders::index');
    $routes->get('pesanan/csv', 'Orders::export');
    $routes->post('pesanan/(:num)/status', 'Orders::updateStatus/$1');
    $routes->post('pesanan/(:num)/hapus', 'Orders::delete/$1');

    $routes->get('reservasi', 'Reservations::index');
    $routes->post('reservasi/(:num)/status', 'Reservations::updateStatus/$1');
    $routes->post('reservasi/(:num)/hapus', 'Reservations::delete/$1');

    $routes->get('menu', 'Menu::index');
    $routes->get('menu/tambah', 'Menu::new');
    $routes->post('menu', 'Menu::create');
    $routes->get('menu/(:num)/edit', 'Menu::edit/$1');
    $routes->post('menu/(:num)', 'Menu::update/$1');
    $routes->post('menu/(:num)/habis', 'Menu::toggleAvailable/$1');
    $routes->post('menu/(:num)/hapus', 'Menu::delete/$1');
});
