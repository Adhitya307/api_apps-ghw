<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('rembesan/check-connection', 'Rembesan\CheckConnection::index');

$routes->post('rembesan/input', 'Rembesan\InputRembesan::index');

$routes->get('rembesan/get_pengukuran', 'Rembesan\GetPengukuran::index');

$routes->get('rembesan/cek-data', 'Rembesan\CekDataController::index');

// Routes untuk hitung Thomson
$routes->get('rembesan/hitungthomson/hitungSemua', 'Rembesan\HitungThomson::hitungSemua');
$routes->get('rembesan/hitungthomson/cekStatus', 'Rembesan\HitungThomson::cekStatus');

$routes->post('rembesan/Rumus-Rembesan', 'Rembesan\RumusRembesan::hitungSemua');

