<?php

namespace App\Controllers\Rembesan;

use App\Controllers\BaseController;
use App\Models\Rembesan\MDataPengukuran;
use App\Models\Rembesan\MThomsonWeir;
use App\Models\Rembesan\MSR;
use App\Models\Rembesan\MBocoranBaru;

class HistoryApi extends BaseController
{
    public function pengukuran()
    {
        $pengukuranModel = new MDataPengukuran();

        $data = $pengukuranModel
            ->select('id, tanggal')
            ->orderBy('tanggal', 'DESC')
            ->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    public function detail($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'ID pengukuran wajib diisi'
            ]);
        }

        $pengukuranModel = new MDataPengukuran();
        $thomsonModel    = new MThomsonWeir();
        $srModel         = new MSR();
        $bocoranModel    = new MBocoranBaru();

        $pengukuran = $pengukuranModel->find($id);
        if (!$pengukuran) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Data pengukuran tidak ditemukan'
            ]);
        }

        // === TMA ===
        $tma = $pengukuran['tma_waduk'] ?? null;

        // === Thomson ===
        $thomson = $thomsonModel->where('pengukuran_id', $id)->first();

        // === SR (loop sr_1 ... sr_106) ===
        $srRow = $srModel->where('pengukuran_id', $id)->first();
        $srData = [];
        if ($srRow) {
            for ($i = 1; $i <= 106; $i++) {
                $kodeKey = "sr_" . $i . "_kode";
                $nilaiKey = "sr_" . $i . "_nilai";

                if (array_key_exists($kodeKey, $srRow) && array_key_exists($nilaiKey, $srRow)) {
                    $srData[] = [
                        'nama'  => "SR_" . $i,
                        'kode'  => $srRow[$kodeKey] ?? "-",
                        'nilai' => $srRow[$nilaiKey] ?? "-"
                    ];
                }
            }
        }

// === Bocoran Baru ===
$bocoranRow = $bocoranModel->where('pengukuran_id', $id)->first();
$bocoran = null;
if ($bocoranRow) {
    $bocoran = [
        'elv_624_t1'      => $bocoranRow['elv_624_t1'] ?? null,
        'elv_624_t1_kode' => $bocoranRow['elv_624_t1_kode'] ?? null,

        'elv_615_t2'      => $bocoranRow['elv_615_t2'] ?? null,
        'elv_615_t2_kode' => $bocoranRow['elv_615_t2_kode'] ?? null,

        'pipa_p1'         => $bocoranRow['pipa_p1'] ?? null,
        'pipa_p1_kode'    => $bocoranRow['pipa_p1_kode'] ?? null,
    ];
}


        // === Return JSON ===
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'tma'     => $tma,
                'thomson' => $thomson,
                'sr'      => $srData,
                'bocoran' => $bocoran
            ]
        ]);
    }
}
