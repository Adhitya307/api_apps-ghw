<?php

namespace App\Controllers\Rembesan;

use App\Controllers\BaseController;
use App\Helpers\Rembesan\AnalisaLookBurtHelper;
use App\Controllers\Rembesan\ThomsonController;
use App\Controllers\Rembesan\SRController;
use App\Controllers\Rembesan\BocoranBaruController;
use App\Controllers\Rembesan\IntiGaleryController;
use App\Controllers\Rembesan\SpillwayController;
use App\Controllers\Rembesan\TebingKananController;
use App\Controllers\Rembesan\TotalBocoranController;
use App\Controllers\Rembesan\BatasMaksimalController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Rembesan\AnalisaLookBurtModel;

class RumusRembesan extends BaseController
{
    use ResponseTrait;

    protected $lookBurtHelper;
    protected $lookBurtModel;

    public function __construct()
    {
        $this->lookBurtHelper = new AnalisaLookBurtHelper();
        $this->lookBurtModel  = new AnalisaLookBurtModel();
    }

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
            $results['Thomson'] = ($hasilThomson['success'] ?? false)
                ? "Perhitungan Thomson berhasil"
                : "Perhitungan Thomson gagal: " . ($hasilThomson['message'] ?? 'Tidak diketahui');
            if (!($hasilThomson['success'] ?? false)) $allSuccess = false;

            // 🔹 SR
            $srCtrl = new SRController();
            $hasilSR = $srCtrl->hitung($pengukuran_id, true);
            $results['SR'] = (($hasilSR['status'] ?? '') === 'success')
                ? "Perhitungan SR berhasil"
                : "Perhitungan SR gagal: " . ($hasilSR['msg'] ?? 'Data SR tidak ditemukan');
            if (($hasilSR['status'] ?? '') !== 'success') $allSuccess = false;

            // 🔹 Bocoran Baru
            $bocoranCtrl = new BocoranBaruController();
            $bocoranCtrl->hitungLangsung($pengukuran_id);
            $results['BocoranBaru'] = "Perhitungan Bocoran Baru berhasil (dipicu)";

            // 🔹 Inti Galery
            $intiCtrl  = new IntiGaleryController();
            $hasilInti = $intiCtrl->proses($pengukuran_id);
            $results['IntiGalery'] = $hasilInti !== false
                ? "Perhitungan IntiGalery berhasil"
                : "Perhitungan IntiGalery gagal";
            if ($hasilInti === false) $allSuccess = false;

            // 🔹 Spillway
            $spillwayCtrl = new SpillwayController();
            $hasilSpillway = $spillwayCtrl->proses($pengukuran_id);
            $results['Spillway'] = $hasilSpillway !== false
                ? "Perhitungan Spillway berhasil"
                : "Perhitungan Spillway gagal";
            if ($hasilSpillway === false) $allSuccess = false;

            // 🔹 Tebing Kanan
            $tebingCtrl = new TebingKananController();
            $hasilTebing = $tebingCtrl->proses($pengukuran_id);
            $results['TebingKanan'] = $hasilTebing !== false
                ? "Perhitungan Tebing Kanan berhasil"
                : "Perhitungan Tebing Kanan gagal";
            if ($hasilTebing === false) $allSuccess = false;

            // 🔹 Total Bocoran
            $totalCtrl = new TotalBocoranController();
            $hasilTotal = $totalCtrl->proses($pengukuran_id);
            $results['TotalBocoran'] = $hasilTotal !== false
                ? "Perhitungan Total Bocoran berhasil"
                : "Perhitungan Total Bocoran gagal";
            if ($hasilTotal === false) $allSuccess = false;

            // 🔹 Batas Maksimal
            $batasCtrl = new BatasMaksimalController();
            $tmaData = $batasCtrl->getBatasInternal($pengukuran_id);
            if (!empty($tmaData) && isset($tmaData['tma'], $tmaData['batas'])) {
                $results['BatasMaksimal'] = "Perhitungan Batas Maksimal berhasil";
            } else {
                $results['BatasMaksimal'] = "Perhitungan Batas Maksimal gagal: Data tidak ditemukan";
                $allSuccess = false;
            }

            // 🔹 Analisa Look Burt (langsung pakai helper)
            $hasilLookBurt = $this->lookBurtHelper->hitungLookBurt($pengukuran_id);

            if ($hasilLookBurt) {
                // Bulatkan rembesan_per_m sampai 8 digit
                $hasilLookBurt['rembesan_per_m'] = round($hasilLookBurt['rembesan_per_m'], 8);

                // Simpan / update ke database
                $existing = $this->lookBurtModel
                    ->where('pengukuran_id', $pengukuran_id)
                    ->first();

                if ($existing) {
                    $this->lookBurtModel->update($existing['id'], $hasilLookBurt);
                } else {
                    $this->lookBurtModel->insert($hasilLookBurt);
                }

                $results['AnalisaLookBurt'] = "Perhitungan Analisa Look Burt berhasil";
            } else {
                $results['AnalisaLookBurt'] = "Perhitungan Analisa Look Burt gagal: Data tidak ditemukan";
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
            'status'        => $allSuccess ? 'success' : 'partial_error',
            'pengukuran_id' => $pengukuran_id,
            'messages'      => $results
        ]);
    }
}
