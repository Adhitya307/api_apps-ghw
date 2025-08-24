<?php

namespace App\Controllers\Rembesan;

use App\Controllers\BaseController;
use App\Controllers\Rembesan\ThomsonController;
use App\Controllers\Rembesan\SRController;
use App\Controllers\Rembesan\BocoranBaruController;
use App\Controllers\Rembesan\IntiGaleryController;
use App\Controllers\Rembesan\SpillwayController;
use App\Controllers\Rembesan\TebingKananController;
use App\Controllers\Rembesan\TotalBocoranController;
use App\Controllers\Rembesan\BatasMaksimalController;

class RumusRembesan extends BaseController
{
    /**
     * Trigger semua perhitungan untuk pengukuran tertentu
     *
     * @param int $pengukuran_id
     * @return array
     */
    public function inputDataForId($pengukuran_id)
    {
        helper('batasmaksimal'); // load helper batasmaksimal

        log_message('debug', "[RumusRembesan] START proses untuk ID: {$pengukuran_id}");

        $result = [
            'success'       => true,
            'thomson'       => null,
            'sr'            => null,
            'bocoran'       => null,
            'intigalery'    => null,
            'spillway'      => null,
            'tebingkanan'   => null,
            'totalbocoran'  => null,
            'batasmaksimal' => null,
        ];

        try {
            // 🔹 Thomson
            $thomsonCtrl  = new ThomsonController();
            $hasilThomson = $thomsonCtrl->hitung($pengukuran_id, true);
            $result['thomson'] = $hasilThomson['success'] 
                ? $hasilThomson['thomson'] 
                : ['success' => false, 'message' => $hasilThomson['message']];

            // 🔹 SR
            $srCtrl = new SRController();
            $hasilSR = $srCtrl->hitung($pengukuran_id, true);
            $result['sr'] = ($hasilSR['status'] ?? '') === 'success' 
                ? $hasilSR['data'] 
                : ['success' => false, 'message' => ($hasilSR['msg'] ?? 'Data SR tidak ditemukan')];

            // 🔹 Bocoran Baru
            $bocoranCtrl = new BocoranBaruController();
            $bocoranCtrl->hitungLangsung($pengukuran_id);
            $result['bocoran'] = ['success' => true, 'message' => 'Perhitungan bocoran dipicu'];

            // 🔹 Inti Galery
            $intiCtrl  = new IntiGaleryController();
            $hasilInti = $intiCtrl->proses($pengukuran_id);
            $result['intigalery'] = $hasilInti !== false 
                ? $hasilInti 
                : ['success' => false, 'message' => 'Perhitungan IntiGalery gagal'];

            // 🔹 Spillway
            $spillwayCtrl = new SpillwayController();
            $hasilSpillway = $spillwayCtrl->proses($pengukuran_id);
            $result['spillway'] = $hasilSpillway !== false
                ? $hasilSpillway
                : ['success' => false, 'message' => 'Perhitungan Spillway gagal'];

            // 🔹 Tebing Kanan
            $tebingCtrl = new TebingKananController();
            $hasilTebing = $tebingCtrl->proses($pengukuran_id);
            $result['tebingkanan'] = $hasilTebing !== false
                ? $hasilTebing
                : ['success' => false, 'message' => 'Perhitungan Tebing Kanan gagal'];

            // 🔹 Total Bocoran
            $totalCtrl = new TotalBocoranController();
            $hasilTotal = $totalCtrl->proses($pengukuran_id);
            $result['totalbocoran'] = $hasilTotal !== false
                ? $hasilTotal
                : ['success' => false, 'message' => 'Perhitungan Total Bocoran gagal'];

            // 🔹 Batas Maksimal
            $batasCtrl = new BatasMaksimalController();
            $tmaData = $batasCtrl->getBatasInternal($pengukuran_id);

            if (empty($tmaData) || !isset($tmaData['tma']) || !isset($tmaData['batas'])) {
                $result['batasmaksimal'] = [
                    'success' => false,
                    'message' => "Data TMA waduk tidak ditemukan atau belum diisi untuk pengukuran_id={$pengukuran_id}"
                ];
                log_message('debug', "[RumusRembesan] Batas Maksimal tidak ditemukan untuk pengukuran_id={$pengukuran_id}");
            } else {
                $result['batasmaksimal'] = [
                    'success' => true,
                    'tma_waduk' => (float) $tmaData['tma'],
                    'batas_maksimal' => (float) $tmaData['batas']
                ];
                log_message('debug', "[RumusRembesan] Batas Maksimal diproses: " . json_encode($result['batasmaksimal']));
            }

            log_message('debug', "[RumusRembesan] SELESAI proses untuk ID: {$pengukuran_id}");
            return $result;

        } catch (\Exception $e) {
            $msg = "❌ Exception di RumusRembesan: " . $e->getMessage();
            log_message('error', $msg);
            return ['success' => false, 'message' => $msg];
        }
    }
}
