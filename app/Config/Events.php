<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;
use App\Controllers\Rembesan\SRController;
use App\Controllers\Rembesan\BocoranBaruController;
use App\Controllers\Rembesan\IntiGaleryController;
use App\Controllers\Rembesan\SpillwayController;
use App\Controllers\Rembesan\TebingKananController;
use App\Controllers\Rembesan\TotalBocoranController;
use App\Controllers\Rembesan\BatasMaksimalController;

log_message('debug', "[Events] Loading Events.php...");

// 🔹 PRE_SYSTEM
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

    if (CI_DEBUG && !is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();

        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});

// 🔹 Helper cek kelengkapan sebelum trigger Total Bocoran
if (!function_exists('\Config\checkAndTriggerTotalBocoran')) {
    function checkAndTriggerTotalBocoran($pengukuran_id) {
        $db = \Config\Database::connect();

        $inti = $db->table('p_intigalery')->where('pengukuran_id', $pengukuran_id)->get()->getRow();
        $spillway = $db->table('p_spillway')->where('pengukuran_id', $pengukuran_id)->get()->getRow();
        $tebing = $db->table('p_tebingkanan')->where('pengukuran_id', $pengukuran_id)->get()->getRow();

        if (!empty($inti) && !empty($spillway) && !empty($tebing)) {
            log_message('debug', "[Events] ✅ Semua data lengkap (Inti, Spillway, Tebing) untuk ID {$pengukuran_id}, trigger TotalBocoran");
            Events::trigger('dataTotalBocoran:insert', $pengukuran_id);
        } else {
            log_message('debug', "[Events] ❌ Data belum lengkap untuk TotalBocoran ID {$pengukuran_id}");
        }
    }
}

// 🔹 Listener dataPengukuran:insert
Events::on('dataPengukuran:insert', function ($pengukuran_id) {
    static $processing = [];

    if (in_array($pengukuran_id, $processing)) {
        log_message('debug', "[Events] ❌ ID {$pengukuran_id} sudah diproses, skip trigger lagi");
        return;
    }

    $processing[] = $pengukuran_id;
    log_message('debug', "[Events] Trigger dataPengukuran:insert untuk ID: {$pengukuran_id}");

    try {
        $rumusCtrl = new \App\Controllers\Rembesan\RumusRembesan();
        $result = $rumusCtrl->inputDataForId($pengukuran_id);
        log_message('debug', "[Events] RumusRembesan dijalankan untuk ID: {$pengukuran_id}");

        // 🔹 Hitung Batas Maksimal langsung
        $batasCtrl = new BatasMaksimalController();
        $batasValue = $batasCtrl->getBatasInternal($pengukuran_id);

        if ($batasValue !== null) {
            log_message('debug', "[Events] Batas Maksimal berhasil untuk ID: {$pengukuran_id} => " . json_encode($batasValue));
        } else {
            log_message('debug', "[Events] Batas Maksimal tidak ditemukan untuk ID: {$pengukuran_id}");
        }

    } catch (\Exception $e) {
        log_message('error', "[Events] Error dataPengukuran:insert: " . $e->getMessage());
    }

    // Hapus dari array processing
    $index = array_search($pengukuran_id, $processing);
    if ($index !== false) unset($processing[$index]);
});

// 🔹 Listener dataSR:insert
Events::on('dataSR:insert', function ($pengukuran_id) {
    log_message('debug', "[Events] Trigger dataSR:insert untuk ID: {$pengukuran_id}");
    try {
        $srCtrl = new SRController();
        $srCtrl->hitung($pengukuran_id);
        Events::trigger('dataTebingKanan:insert', $pengukuran_id);
    } catch (\Exception $e) {
        log_message('error', "[Events] Error SRController: " . $e->getMessage());
    }
});

// 🔹 Listener dataIntiGalery:insert
Events::on('dataIntiGalery:insert', function ($pengukuran_id) {
    log_message('debug', "[Events] Trigger dataIntiGalery:insert untuk ID: {$pengukuran_id}");
    try {
        $intiCtrl = new IntiGaleryController();
        $hasil = $intiCtrl->proses($pengukuran_id);
        if ($hasil !== false) {
            log_message('debug', "[Events] IntiGalery berhasil untuk ID: {$pengukuran_id}");
            checkAndTriggerTotalBocoran($pengukuran_id);
        }
    } catch (\Exception $e) {
        log_message('error', "[Events] Error IntiGalery: " . $e->getMessage());
    }
});

// 🔹 Listener dataSpillway:insert
Events::on('dataSpillway:insert', function ($pengukuran_id) {
    log_message('debug', "[Events] Trigger dataSpillway:insert untuk ID: {$pengukuran_id}");
    try {
        $spillwayCtrl = new SpillwayController();
        $hasil = $spillwayCtrl->proses($pengukuran_id);
        if ($hasil !== false) {
            log_message('debug', "[Events] Spillway berhasil untuk ID: {$pengukuran_id}");
            checkAndTriggerTotalBocoran($pengukuran_id);
        }
    } catch (\Exception $e) {
        log_message('error', "[Events] Error Spillway: " . $e->getMessage());
    }
});

// 🔹 Listener dataTebingKanan:insert
Events::on('dataTebingKanan:insert', function ($pengukuran_id) {
    log_message('debug', "[Events] Trigger dataTebingKanan:insert untuk ID: {$pengukuran_id}");
    try {
        $tebingCtrl = new TebingKananController();
        $hasil = $tebingCtrl->proses($pengukuran_id);
        if ($hasil !== false) {
            log_message('debug', "[Events] Tebing Kanan berhasil untuk ID: {$pengukuran_id}");
            checkAndTriggerTotalBocoran($pengukuran_id);
        }
    } catch (\Exception $e) {
        log_message('error', "[Events] Error Tebing Kanan: " . $e->getMessage());
    }
});

// 🔹 Listener dataTotalBocoran:insert
Events::on('dataTotalBocoran:insert', function ($pengukuran_id) {
    log_message('debug', "[Events] Trigger dataTotalBocoran:insert untuk ID: {$pengukuran_id}");
    try {
        $totalCtrl = new TotalBocoranController();
        $hasil = $totalCtrl->proses($pengukuran_id);
        if ($hasil !== false) {
            log_message('debug', "[Events] Total Bocoran berhasil untuk ID: {$pengukuran_id} => " . json_encode($hasil));
        }
    } catch (\Exception $e) {
        log_message('error', "[Events] Error Total Bocoran: " . $e->getMessage());
    }
});
