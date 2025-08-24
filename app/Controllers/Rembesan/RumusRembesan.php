<?php

namespace App\Controllers\Rembesan;

use App\Controllers\BaseController;
use App\Controllers\Rembesan\ThomsonController;
use App\Controllers\Rembesan\SRController;
use App\Controllers\Rembesan\BocoranBaruController;

class RumusRembesan extends BaseController
{
    public function inputDataForId($pengukuran_id)
    {
        log_message('debug', "[RumusRembesan] START proses untuk ID: {$pengukuran_id}");

        $result = [
            'success' => true,
            'thomson' => null,
            'sr'      => null,
            'bocoran' => null,
        ];

        try {
            // 🔹 Panggil ThomsonController
            $thomsonCtrl = new ThomsonController();
            $hasilThomson = $thomsonCtrl->hitung($pengukuran_id, true);

            if (!$hasilThomson['success']) {
                log_message('error', "[RumusRembesan] Thomson gagal: " . $hasilThomson['message']);
                $result['thomson'] = ['success' => false, 'message' => $hasilThomson['message']];
            } else {
                log_message('debug', "[RumusRembesan] Thomson berhasil: " . json_encode($hasilThomson['thomson']));
                $result['thomson'] = $hasilThomson['thomson'];
            }

            // 🔹 Panggil SRController
            $srCtrl = new SRController();
            $hasilSR = $srCtrl->hitung($pengukuran_id, true);

            if (!$hasilSR['status'] || $hasilSR['status'] !== 'success') {
                log_message('error', "[RumusRembesan] SR gagal: " . ($hasilSR['msg'] ?? 'Unknown error'));
                $result['sr'] = ['success' => false, 'message' => ($hasilSR['msg'] ?? 'Unknown error')];
            } else {
                log_message('debug', "[RumusRembesan] SR berhasil: " . json_encode($hasilSR['data']));
                $result['sr'] = $hasilSR['data'];
            }

            // 🔹 Trigger BocoranBaruController
            $bocoranCtrl = new BocoranBaruController();
            $bocoranCtrl->hitungLangsung($pengukuran_id);
            log_message('debug', "[RumusRembesan] BocoranBaruController trigger berhasil untuk ID: {$pengukuran_id}");
            $result['bocoran'] = ['success' => true, 'message' => 'Perhitungan bocoran dipicu'];

            log_message('debug', "[RumusRembesan] SELESAI proses untuk ID: {$pengukuran_id}");
            return $result;

        } catch (\Exception $e) {
            $msg = "❌ Exception di RumusRembesan: " . $e->getMessage();
            log_message('error', $msg);
            return ['success' => false, 'message' => $msg];
        }
    }
}
