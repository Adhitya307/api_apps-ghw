<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;
use App\Controllers\Rembesan\SRController;
use App\Controllers\Rembesan\BocoranBaruController;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 */
Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();

        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});

/*
 * --------------------------------------------------------------------
 * Custom Application Events
 * --------------------------------------------------------------------
 */

// 🔹 Listener untuk Thomson -> memicu RumusRembesan (yang sudah memanggil BocoranBaruController)
Events::on('dataThomson:insert', function($pengukuran_id) {
    log_message('debug', "🎯 Event dataThomson:insert triggered for ID: {$pengukuran_id}");
    
    try {
        $rumusController = new \App\Controllers\Rembesan\RumusRembesan();
        $result = $rumusController->inputDataForId($pengukuran_id);
        
        if ($result && isset($result['success']) && $result['success']) {
            log_message('debug', "✅ Perhitungan RumusRembesan (Thomson, SR, Bocoran) sukses untuk ID: {$pengukuran_id}");
        } else {
            log_message('error', "❌ Perhitungan RumusRembesan gagal untuk ID: {$pengukuran_id}");
        }
    } catch (\Exception $e) {
        log_message('error', "🔥 Error in dataThomson:insert event: " . $e->getMessage());
    }
});

// 🔹 Listener untuk SR
Events::on('dataSR:insert', function($pengukuran_id) {
    log_message('debug', "[Events] Trigger dataSR:insert untuk ID: {$pengukuran_id}");
    try {
        $srCtrl = new SRController();
        $srCtrl->hitung($pengukuran_id);
        log_message('debug', "[Events] SRController::hitung berhasil untuk ID: {$pengukuran_id}");
    } catch (\Exception $e) {
        log_message('error', "[Events] SRController gagal: " . $e->getMessage());
    }
});

// 🔹 Listener untuk dataPengukuran:insert
Events::on('dataPengukuran:insert', function($pengukuran_id) {
    log_message('debug', "[Events] Trigger dataPengukuran:insert untuk ID: {$pengukuran_id}");
    try {
        // Bisa panggil RumusRembesan agar langsung hitung semua
        $rumusController = new \App\Controllers\Rembesan\RumusRembesan();
        $rumusController->inputDataForId($pengukuran_id);
        log_message('debug', "[Events] RumusRembesan dijalankan setelah dataPengukuran disimpan ID: {$pengukuran_id}");
    } catch (\Exception $e) {
        log_message('error', "[Events] Error dataPengukuran:insert: " . $e->getMessage());
    }
});

