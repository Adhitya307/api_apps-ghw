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

// === History API (lama, hanya id + tanggal) ===
$routes->group('api/rembesan', function($routes) {
    $routes->get('pengukuran', 'Rembesan\HistoryApi::pengukuran');
    $routes->get('detail/(:num)', 'Rembesan\HistoryApi::detail/$1');
});

// === Backup API (baru, field lengkap) ===
$routes->group('api/rembesan/backup', function($routes) {
    $routes->get('pengukuran', 'Rembesan\BackupApi::pengukuran');
    $routes->get('thomson', 'Rembesan\BackupApi::thomson');
    $routes->get('sr', 'Rembesan\BackupApi::sr');
    $routes->get('bocoran', 'Rembesan\BackupApi::bocoran');
});
