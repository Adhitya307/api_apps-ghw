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
use CodeIgniter\API\ResponseTrait;

class RumusRembesan extends BaseController
{
    use ResponseTrait;

    /**
     * Endpoint API: Hitung semua perhitungan
     * Body JSON: { "pengukuran_id": 123 }
     */
    public function hitungSemua()
    {
        $json = $this->request->getJSON(true);
        $pengukuran_id = $json['pengukuran_id'] ?? null;

        if (!$pengukuran_id) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'pengukuran_id wajib diisi'
            ], 400);
        }

        log_message('debug', "[HitungSemua] START proses semua perhitungan untuk ID={$pengukuran_id}");

        $results = [];
        $allSuccess = true;

        try {
            // 🔹 Thomson
            $thomsonCtrl  = new ThomsonController();
            $hasilThomson = $thomsonCtrl->hitung($pengukuran_id, true);
            if ($hasilThomson['success'] ?? false) {
                $results['Thomson'] = "Perhitungan Thomson berhasil";
                log_message('debug', "[HitungSemua] Thomson OK untuk ID={$pengukuran_id}");
            } else {
                $results['Thomson'] = "Perhitungan Thomson gagal: " . ($hasilThomson['message'] ?? 'Tidak diketahui');
                log_message('error', "[HitungSemua] Thomson GAGAL | ID={$pengukuran_id} | Detail: " . json_encode($hasilThomson));
                $allSuccess = false;
            }

            // 🔹 SR
            $srCtrl = new SRController();
            $hasilSR = $srCtrl->hitung($pengukuran_id, true);
            if (($hasilSR['status'] ?? '') === 'success') {
                $results['SR'] = "Perhitungan SR berhasil";
                log_message('debug', "[HitungSemua] SR OK untuk ID={$pengukuran_id}");
            } else {
                $results['SR'] = "Perhitungan SR gagal: " . ($hasilSR['msg'] ?? 'Data SR tidak ditemukan');
                log_message('error', "[HitungSemua] SR GAGAL | ID={$pengukuran_id} | Detail: " . json_encode($hasilSR));
                $allSuccess = false;
            }

            // 🔹 Bocoran Baru
            $bocoranCtrl = new BocoranBaruController();
            $bocoranCtrl->hitungLangsung($pengukuran_id);
            $results['BocoranBaru'] = "Perhitungan Bocoran Baru berhasil (dipicu)";
            log_message('debug', "[HitungSemua] BocoranBaru OK untuk ID={$pengukuran_id}");

            // 🔹 Inti Galery
            $intiCtrl  = new IntiGaleryController();
            $hasilInti = $intiCtrl->proses($pengukuran_id);
            if ($hasilInti !== false) {
                $results['IntiGalery'] = "Perhitungan IntiGalery berhasil";
                log_message('debug', "[HitungSemua] IntiGalery OK untuk ID={$pengukuran_id}");
            } else {
                $results['IntiGalery'] = "Perhitungan IntiGalery gagal";
                log_message('error', "[HitungSemua] IntiGalery GAGAL | ID={$pengukuran_id}");
                $allSuccess = false;
            }

            // 🔹 Spillway
            $spillwayCtrl = new SpillwayController();
            $hasilSpillway = $spillwayCtrl->proses($pengukuran_id);
            if ($hasilSpillway !== false) {
                $results['Spillway'] = "Perhitungan Spillway berhasil";
                log_message('debug', "[HitungSemua] Spillway OK untuk ID={$pengukuran_id}");
            } else {
                $results['Spillway'] = "Perhitungan Spillway gagal";
                log_message('error', "[HitungSemua] Spillway GAGAL | ID={$pengukuran_id}");
                $allSuccess = false;
            }

            // 🔹 Tebing Kanan
            $tebingCtrl = new TebingKananController();
            $hasilTebing = $tebingCtrl->proses($pengukuran_id);
            if ($hasilTebing !== false) {
                $results['TebingKanan'] = "Perhitungan Tebing Kanan berhasil";
                log_message('debug', "[HitungSemua] TebingKanan OK untuk ID={$pengukuran_id}");
            } else {
                $results['TebingKanan'] = "Perhitungan Tebing Kanan gagal";
                log_message('error', "[HitungSemua] TebingKanan GAGAL | ID={$pengukuran_id}");
                $allSuccess = false;
            }

            // 🔹 Total Bocoran
            $totalCtrl = new TotalBocoranController();
            $hasilTotal = $totalCtrl->proses($pengukuran_id);
            if ($hasilTotal !== false) {
                $results['TotalBocoran'] = "Perhitungan Total Bocoran berhasil";
                log_message('debug', "[HitungSemua] TotalBocoran OK untuk ID={$pengukuran_id}");
            } else {
                $results['TotalBocoran'] = "Perhitungan Total Bocoran gagal";
                log_message('error', "[HitungSemua] TotalBocoran GAGAL | ID={$pengukuran_id}");
                $allSuccess = false;
            }

            // 🔹 Batas Maksimal
            $batasCtrl = new BatasMaksimalController();
            $tmaData = $batasCtrl->getBatasInternal($pengukuran_id);
            if (!empty($tmaData) && isset($tmaData['tma'], $tmaData['batas'])) {
                $results['BatasMaksimal'] = "Perhitungan Batas Maksimal berhasil";
                log_message('debug', "[HitungSemua] BatasMaksimal OK untuk ID={$pengukuran_id}");
            } else {
                $results['BatasMaksimal'] = "Perhitungan Batas Maksimal gagal: Data tidak ditemukan";
                log_message('error', "[HitungSemua] BatasMaksimal GAGAL | ID={$pengukuran_id} | Data: " . json_encode($tmaData));
                $allSuccess = false;
            }

        } catch (\Throwable $e) {
            log_message('error', "[HitungSemua] Exception global | ID={$pengukuran_id} | Error: " . $e->getMessage());
            return $this->respond([
                'status'  => 'error',
                'message' => 'Exception: ' . $e->getMessage()
            ], 500);
        }

        log_message('debug', "[HitungSemua] SELESAI proses untuk ID={$pengukuran_id}");

        return $this->respond([
            'status'  => $allSuccess ? 'success' : 'partial_error',
            'pengukuran_id' => $pengukuran_id,
            'messages' => $results
        ]);
    }
}
