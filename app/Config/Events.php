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