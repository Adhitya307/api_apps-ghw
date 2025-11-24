<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// ======================================================================
// REMBESAN ROUTES
// ======================================================================
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
    // Data Input
    $routes->get('pengukuran', 'Rembesan\BackupApi::pengukuran');
    $routes->get('thomson', 'Rembesan\BackupApi::thomson');
    $routes->get('sr', 'Rembesan\BackupApi::sr');
    $routes->get('bocoran', 'Rembesan\BackupApi::bocoran');
    
    // Data Hasil Perhitungan (P_)
    $routes->get('p_batasmaksimal', 'Rembesan\BackupApi::p_batasmaksimal');
    $routes->get('p_bocoran_baru', 'Rembesan\BackupApi::p_bocoran_baru');
    $routes->get('p_intigalery', 'Rembesan\BackupApi::p_intigalery');
    $routes->get('p_spillway', 'Rembesan\BackupApi::p_spillway');
    $routes->get('p_sr', 'Rembesan\BackupApi::p_sr');
    $routes->get('p_tebingkanan', 'Rembesan\BackupApi::p_tebingkanan');
    $routes->get('p_thomson_weir', 'Rembesan\BackupApi::p_thomson_weir');
    $routes->get('p_totalbocoran', 'Rembesan\BackupApi::p_totalbocoran');
});

$routes->group('rembesan/lookburt', ['namespace' => 'App\Controllers\Rembesan'], function($routes) {
    $routes->get('hitung/(:num)', 'AnalisaLookBurt::hitung/$1');
    $routes->get('hitung-semua', 'AnalisaLookBurt::hitungSemua');
    $routes->get('/', 'AnalisaLookBurt::index');
});

$routes->get('api/rembesan/analisa_look_burt', 'Rembesan\BackupApi::analisa_look_burt');
$routes->get('rembesan/get_inti_gallery', 'Rembesan\IntiGaleryController::getIntiGallery');

// ======================================================================
// DOM BODY / HDM ROUTES
// ======================================================================
$routes->post('dombody/input', 'DomBody\Inputdombody::index');
$routes->get('dombody/input', 'DomBody\Inputdombody::index');
$routes->get('dombody/get-pengukuran', 'DomBody\GetPengukuranHdm::index');
$routes->post('dombody/get-pengukuran', 'DomBody\GetPengukuranHdm::index');

$routes->post('dombody/hitung/elv600', 'DomBody\Hitungpergerakan::hitungElv600');
$routes->post('dombody/hitung/elv625', 'DomBody\Hitungpergerakan::hitungElv625');

// DomBody API Routes
$routes->group('api/dambody', function($routes) {
    $routes->get('pengukuran', 'DomBody\DamBodyApi::pengukuran');
    $routes->get('pembacaan-625', 'DomBody\DamBodyApi::pembacaan_625');
    $routes->get('pembacaan-600', 'DomBody\DamBodyApi::pembacaan_600');
    $routes->get('depth-625', 'DomBody\DamBodyApi::depth_625');
    $routes->get('depth-600', 'DomBody\DamBodyApi::depth_600');
    $routes->get('initial-625', 'DomBody\DamBodyApi::initial_625');
    $routes->get('initial-600', 'DomBody\DamBodyApi::initial_600');
    $routes->get('pergerakan-625', 'DomBody\DamBodyApi::pergerakan_625');
    $routes->get('pergerakan-600', 'DomBody\DamBodyApi::pergerakan_600');
    
    // ✅ TAMBAHKAN ROUTE AMBANG BATAS DI SINI
    $routes->get('ambang-batas-625-h1', 'DomBody\DamBodyApi::ambang_batas_625_h1');
    $routes->get('ambang-batas-625-h2', 'DomBody\DamBodyApi::ambang_batas_625_h2');
    $routes->get('ambang-batas-625-h3', 'DomBody\DamBodyApi::ambang_batas_625_h3');
    $routes->get('ambang-batas-600-h1', 'DomBody\DamBodyApi::ambang_batas_600_h1');
    $routes->get('ambang-batas-600-h2', 'DomBody\DamBodyApi::ambang_batas_600_h2');
    $routes->get('ambang-batas-600-h3', 'DomBody\DamBodyApi::ambang_batas_600_h3');
    $routes->get('ambang-batas-600-h4', 'DomBody\DamBodyApi::ambang_batas_600_h4');
    $routes->get('ambang-batas-600-h5', 'DomBody\DamBodyApi::ambang_batas_600_h5');
    
    $routes->get('all-data', 'DomBody\DamBodyApi::all_data');
    $routes->get('by-pengukuran/(:num)', 'DomBody\DamBodyApi::by_pengukuran/$1');
    $routes->get('sync', 'DomBody\DamBodyApi::sync');
});

// ======================================================================
// BTM ROUTES
// ======================================================================
$routes->post('btm/input', 'Btm\InputDataBtm::index');
$routes->get('btm/get-pengukuran', 'Btm\InputDataBtm::getPengukuran');
$routes->get('btm/get-data', 'Btm\InputDataBtm::getData');
$routes->get('btm/get-pengukuran-bulan-ini', 'Btm\GetPengukuranBtm::index');

// ✅ ROUTES UNTUK HITUNG BTM - YANG INI SAJA (SUDAH DIPERBAIKI)
$routes->group('btm', ['namespace' => 'App\Controllers\Btm'], function($routes) {
    // Hitung BT spesifik - GUNAKAN METHOD YANG ADA DI CONTROLLER
    $routes->post('hitung/bubbletilt', 'Hitungbtm::hitungBubbleTilt');
    $routes->get('hitung/bubbletilt', 'Hitungbtm::hitungBubbleTilt');        // ✅ PERBAIKI: hitungBt
    
    // Hitung semua BT - GUNAKAN METHOD YANG ADA
    $routes->post('hitung/semua-bt', 'Hitungbtm::hitungSemua');       // ✅ PERBAIKI: hitungSemua
    $routes->get('hitung/semua-bt', 'Hitungbtm::hitungSemua');        // ✅ PERBAIKI: hitungSemua
    
    // Get data bacaan - TAMBAHKAN METHOD INI DI CONTROLLER
    $routes->get('hitung/data-bacaan', 'Hitungbtm::getDataBacaan');
    $routes->post('hitung/data-bacaan', 'Hitungbtm::getDataBacaan');
    
    // Get data perhitungan - TAMBAHKAN METHOD INI DI CONTROLLER  
    $routes->get('hitung/data-perhitungan', 'Hitungbtm::getDataPerhitungan');
    $routes->post('hitung/data-perhitungan', 'Hitungbtm::getDataPerhitungan');
    
    // Health check dan testing - METHOD SUDAH ADA
    $routes->get('hitung/health', 'Hitungbtm::healthCheck');
    $routes->get('hitung/debug/(:num)', 'Hitungbtm::debugPerhitungan/$1'); // ✅ GUNAKAN YANG ADA
    $routes->get('hitung/recalculate-all', 'Hitungbtm::recalculateAll');   // ✅ GUNAKAN YANG ADA
});

// === BTM API ROUTES - TAMBAHKAN INI ===
$routes->group('api/btm', ['namespace' => 'App\Controllers\Btm'], function($routes) {
    // Pengukuran
    $routes->get('pengukuran', 'BtmApiController::pengukuran');
    
    // Bacaan
    $routes->get('bacaan_bt1', 'BtmApiController::bacaan_bt1');
    $routes->get('bacaan_bt2', 'BtmApiController::bacaan_bt2');
    $routes->get('bacaan_bt3', 'BtmApiController::bacaan_bt3');
    $routes->get('bacaan_bt4', 'BtmApiController::bacaan_bt4');
    $routes->get('bacaan_bt5', 'BtmApiController::bacaan_bt5');
    $routes->get('bacaan_bt6', 'BtmApiController::bacaan_bt6');
    $routes->get('bacaan_bt7', 'BtmApiController::bacaan_bt7');
    $routes->get('bacaan_bt8', 'BtmApiController::bacaan_bt8');
    
    // Perhitungan
    $routes->get('perhitungan_bt1', 'BtmApiController::perhitungan_bt1');
    $routes->get('perhitungan_bt2', 'BtmApiController::perhitungan_bt2');
    $routes->get('perhitungan_bt3', 'BtmApiController::perhitungan_bt3');
    $routes->get('perhitungan_bt4', 'BtmApiController::perhitungan_bt4');
    $routes->get('perhitungan_bt5', 'BtmApiController::perhitungan_bt5');
    $routes->get('perhitungan_bt6', 'BtmApiController::perhitungan_bt6');
    $routes->get('perhitungan_bt7', 'BtmApiController::perhitungan_bt7');
    $routes->get('perhitungan_bt8', 'BtmApiController::perhitungan_bt8');
    
    // Scatter
    $routes->get('scatter_bt1', 'BtmApiController::scatter_bt1');
    $routes->get('scatter_bt2', 'BtmApiController::scatter_bt2');
    $routes->get('scatter_bt3', 'BtmApiController::scatter_bt3');
    $routes->get('scatter_bt4', 'BtmApiController::scatter_bt4');
    $routes->get('scatter_bt6', 'BtmApiController::scatter_bt6');
    $routes->get('scatter_bt7', 'BtmApiController::scatter_bt7');
    $routes->get('scatter_bt8', 'BtmApiController::scatter_bt8');
    
    // All Data & Sync
    $routes->get('all_data', 'BtmApiController::all_data');
    $routes->get('by_pengukuran/(:num)', 'BtmApiController::by_pengukuran/$1');
    $routes->get('sync', 'BtmApiController::sync');
});

// ======================================================================
// EXTENSO ROUTES
// ======================================================================
$routes->group('exstenso', function($routes) {
    
    // Input Data Exstenso
    $routes->post('inputdata', 'Exstenso\InputDataExstenso::index');
    $routes->get('inputdata', 'Exstenso\InputDataExstenso::index'); // Untuk testing GET
    
    // Get Pengukuran Exstenso
    $routes->get('getpengukuran', 'Exstenso\GetPengukuranExstenso::index');
    $routes->get('getpengukuran/getAll', 'Exstenso\GetPengukuranExstenso::getAll');
    $routes->get('getpengukuran/getByPeriod', 'Exstenso\GetPengukuranExstenso::getByPeriod');
    $routes->get('getpengukuran/getById/(:num)', 'Exstenso\GetPengukuranExstenso::getById/$1');
    $routes->get('getpengukuran/getById', 'Exstenso\GetPengukuranExstenso::getById');
    
    // Get Data Specific (pembacaan, readings, dll)
    $routes->get('getdata', 'Exstenso\InputDataExstenso::getData');
    
});

$routes->post('exstenso/hitung-deformasi-ex1', 'Exstenso\PerhitunganExtenso::HitungDeformasiEx1');
$routes->post('exstenso/hitung-deformasi-ex2', 'Exstenso\PerhitunganExtenso::HitungDeformasiEx2');
$routes->post('exstenso/hitung-deformasi-ex3', 'Exstenso\PerhitunganExtenso::HitungDeformasiEx3');
$routes->post('exstenso/hitung-deformasi-ex4', 'Exstenso\PerhitunganExtenso::HitungDeformasiEx4');
$routes->post('exstenso/hitung-semua-deformasi', 'Exstenso\PerhitunganExtenso::HitungSemuaDeformasi');


// Routes untuk Left Piezometer
$routes->group('leftpiez', function($routes) {
    // Input Data - POST untuk semua operasi
    $routes->post('inputdata', 'Leftpiez\InputdataLeftpiez::index');
    
    // Get Data Pengukuran - GET (berbagai metode)
    $routes->get('getpengukuran', 'Leftpiez\GetPengukuranLeftpiez::index'); // Data bulan ini
    $routes->get('getpengukuran/all', 'Leftpiez\GetPengukuranLeftpiez::getAll'); // Semua data
    $routes->get('getpengukuran/period', 'Leftpiez\GetPengukuranLeftpiez::getByPeriod'); // By period
    $routes->get('getpengukuran/(:num)', 'Leftpiez\GetPengukuranLeftpiez::getById/$1'); // By ID
    
    // Get Data Pembacaan - GET
    $routes->get('getdata', 'Leftpiez\InputdataLeftpiez::getData');
    
    // Get All Data Pembacaan - GET
    $routes->get('getalldata', 'Leftpiez\InputdataLeftpiez::getAllData');
});

$routes->group('leftpiez', function($r) {
    // Hitung satu L tertentu, kolom dikirim via query string ?kolom=l_01
    $r->get('hitung-satu/(:num)', 'LeftPiez\HitungLeft::hitungSatu/$1');

    // Hitung semua L01-L10 + SPZ02
    $r->get('hitung-semua/(:num)', 'LeftPiez\HitungLeft::hitungSemua/$1');

    // Preview (POST)
    $r->post('preview', 'LeftPiez\HitungLeft::preview');

    // Optional: hitungByPengukuran lama, bisa tetap ada jika ingin dipakai
    $r->get('hitung/(:num)', 'LeftPiez\HitungLeft::hitungByPengukuran/$1');
    
    // ===========================================================================
    // ROUTES UNTUK CONTROLLER GABUNGAN - PERHITUNGAN DARI PEMBACAAN
    // ===========================================================================
    $r->get('hitung/hitunglokasi/(:num)/(:any)', 'LeftPiez\HitungLeft::hitungLokasi/$1/$2');
    $r->get('hitung/hitungsemua/(:num)', 'LeftPiez\HitungLeft::hitungSemuaDariPembacaan/$1');
    
    // ===========================================================================
    // ROUTES UNTUK CONTROLLER GABUNGAN - PERHITUNGAN RUMUS Elv_Piez
    // ===========================================================================
    $r->get('hitung-rumus/semua/(:num)', 'LeftPiez\HitungLeft::hitungSemuaRumus/$1');
    $r->get('hitung-rumus/(:any)/(:num)', 'LeftPiez\HitungLeft::hitungRumus/$1/$2');
    $r->post('simpan-rumus', 'LeftPiez\HitungLeft::simpanSemuaRumus');
    $r->post('simpan-rumus/(:any)', 'LeftPiez\HitungLeft::simpanRumus/$1');
    $r->get('data-rumus', 'LeftPiez\HitungLeft::getDataRumus');
    $r->get('data-rumus/(:any)', 'LeftPiez\HitungLeft::getDataRumus/$1');
    $r->put('update-rumus/(:any)', 'LeftPiez\HitungLeft::updateDataRumus/$1');
    
    // ===========================================================================
    // ROUTES BARU UNTUK IREADING A & B
    // ===========================================================================
    $r->post('ireading/(:any)', 'LeftPiez\HitungLeft::simpanIreading/$1'); // A atau B
    $r->get('ireading/(:any)', 'LeftPiez\HitungLeft::getIreading/$1'); // A atau B
    
    // ===========================================================================
    // ROUTES BARU UNTUK PERHITUNGAN DENGAN IREADING
    // ===========================================================================
    $r->get('hitung-dengan-ireading/(:any)/(:num)/(:any)', 'LeftPiez\HitungLeft::hitungDenganIreading/$1/$2/$3'); // piezometer/id/source
    
    // ===========================================================================
    // ROUTES BARU UNTUK INSERT DATA PERHITUNGAN
    // ===========================================================================
    $r->post('insert-rumus/(:any)', 'LeftPiez\HitungLeft::insertRumus/$1');
    
    // ===========================================================================
    // ROUTES BARU UNTUK GET NILAI METRIK
    // ===========================================================================
    $r->get('nilai-metrik', 'LeftPiez\HitungLeft::getNilaiMetrik');
    $r->get('nilai-metrik/(:any)', 'LeftPiez\HitungLeft::getNilaiMetrik/$1');
    
    // ===========================================================================
    // ROUTES BARU UNTUK UPDATE LENGKAP
    // ===========================================================================
    $r->put('update-rumus-lengkap/(:any)', 'LeftPiez\HitungLeft::updateRumusLengkap/$1');
    
    // ===========================================================================
    // DASHBOARD & UTILITY
    // ===========================================================================
    $r->get('dashboard/(:num)', 'LeftPiez\HitungLeft::dashboard/$1');
    $r->get('status/(:num)', 'LeftPiez\HitungLeft::dashboard/$1'); // alias untuk dashboard
});


// Routes untuk Right Piezo
$routes->group('rightpiezo', function($routes) {
    
    // Routes untuk Inputdatarightpiez Controller
    $routes->group('input', function($routes) {
        $routes->post('', 'Rightpiezo\Inputdatarightpiez::index');
        $routes->post('pengukuran', 'Rightpiezo\Inputdatarightpiez::index');
        $routes->post('update_tma', 'Rightpiezo\Inputdatarightpiez::index');
        $routes->post('pembacaan/(:segment)', 'Rightpiezo\Inputdatarightpiez::savePembacaan/$1');
        $routes->post('metrik', 'Rightpiezo\Inputdatarightpiez::saveMetrik');
        $routes->post('ireading_atas', 'Rightpiezo\Inputdatarightpiez::saveIreadingAtas');
        $routes->post('perhitungan_tengah', 'Rightpiezo\Inputdatarightpiez::savePerhitunganTengah');
        $routes->post('elevasi_dasar', 'Rightpiezo\Inputdatarightpiez::saveElevasiDasar');
        
        // GET routes untuk Inputdatarightpiez
        $routes->get('pengukuran', 'Rightpiezo\Inputdatarightpiez::getPengukuran');
        $routes->get('pengukuran/(:num)', 'Rightpiezo\Inputdatarightpiez::getPengukuranById/$1');
        $routes->get('data', 'Rightpiezo\Inputdatarightpiez::getData');
        $routes->get('all-data', 'Rightpiezo\Inputdatarightpiez::getAllData');
        $routes->get('additional-data', 'Rightpiezo\Inputdatarightpiez::getAdditionalData');
    });
    
    // Routes untuk GetPengukuranRightpiez Controller
    $routes->group('get', function($routes) {
        $routes->get('pengukuran', 'Rightpiezo\GetPengukuranRightpiez::index');
        $routes->get('pengukuran/all', 'Rightpiezo\GetPengukuranRightpiez::getAll');
        $routes->get('pengukuran/period', 'Rightpiezo\GetPengukuranRightpiez::getByPeriod');
        $routes->get('pengukuran/(:num)', 'Rightpiezo\GetPengukuranRightpiez::getById/$1');
        $routes->get('pembacaan', 'Rightpiezo\GetPengukuranRightpiez::getPembacaanByPengukuranId');
        $routes->get('metrik', 'Rightpiezo\GetPengukuranRightpiez::getMetrikByPengukuranId');
        $routes->get('ireading-atas', 'Rightpiezo\GetPengukuranRightpiez::getIreadingAtasByPengukuranId');
        $routes->get('perhitungan-tengah', 'Rightpiezo\GetPengukuranRightpiez::getPerhitunganTengahByPengukuranId');
        $routes->get('elevasi-dasar', 'Rightpiezo\GetPengukuranRightpiez::getElevasiDasarByPengukuranId');
        $routes->get('complete-data', 'Rightpiezo\GetPengukuranRightpiez::getCompleteDataByPengukuranId');
    });
    
    // ✅ ✅ ✅ PASTIKAN ROUTES HITUNG ADA DI DALAM GROUP RIGHTPIEZO ✅ ✅ ✅
    $routes->group('hitung', function($routes) {
        $routes->get('all', 'Rightpiezo\Hitungright::hitungAll');
        $routes->get('lokasi', 'Rightpiezo\Hitungright::hitungLokasi');
        $routes->get('status', 'Rightpiezo\Hitungright::getStatus');
        $routes->get('hasil', 'Rightpiezo\Hitungright::getHasil');
        $routes->get('trigger', 'Rightpiezo\Hitungright::triggerHitung');
        $routes->get('test', 'Rightpiezo\Hitungright::testRumus');
        $routes->get('trigger/(:num)/(:segment)', 'Rightpiezo\Hitungright::triggerHitung/$1/$2');
    });
});