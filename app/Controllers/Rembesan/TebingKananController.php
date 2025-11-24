<?php
namespace App\Controllers\Rembesan;

use App\Controllers\BaseController;
use App\Models\Rembesan\TebingKananModel;

class TebingKananController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new TebingKananModel();
        helper('rembesan/rumus_tebingkanan'); // berisi hitungSrTebingKanan, getAmbangTebingKanan, cariAmbangTebingKanan
        helper('rembesan/rumus_sr'); // fungsi perhitunganQ_sr()
    }

    public function proses($pengukuranId)
    {
        log_message('debug', "[TebingKanan] ▶️ Mulai proses untuk ID {$pengukuranId}");

        $db = \Config\Database::connect();

        // 🔹 1) Ambil data dasar pengukuran
        $pengukuran = $db->table('t_data_pengukuran')
            ->select('id, tma_waduk')
            ->where('id', $pengukuranId)
            ->get()
            ->getRowArray();

        if (!$pengukuran) {
            log_message('error', "[TebingKanan] ❌ Data pengukuran tidak ditemukan untuk ID {$pengukuranId}");
            return false;
        }

        $tma = (float)$pengukuran['tma_waduk'];

        // 🔹 2) Ambil data SR hasil perhitungan
        $dataSr = $db->table('p_sr')
            ->where('pengukuran_id', $pengukuranId)
            ->get()
            ->getRowArray();

        // =============================== PERBAIKAN DISINI ===============================
        // ❗ Kalau SR gagal dan datanya tidak ada → jangan return false,
        // tetap lanjut proses hitung ambang & ambil B5 agar B5 masuk ke DB.
        if (!$dataSr) {
            log_message('warning', "[TebingKanan] ⚠️ Data SR TIDAK ADA, SR akan diset default 0");
            $srTotal = 0; // default untuk kasus SR gagal
        } else {
            // 🔹 Daftar SR yang dipakai untuk tebing kanan
            $srFields = [
                1,40,66,67,68,69,70,71,72,73,74,75,76,77,78,
                79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,
                94,95,96,97,98,99,100,101,102,103,104,105,106
            ];

            $srTotal = hitungSrTebingKanan($dataSr, $srFields);
        }
        // =============================================================================


        // 🔹 3) Ambil nilai ambang dari Excel
        $filePath = FCPATH . 'assets/excel/tabel_ambang.xlsx';
        if (!is_file($filePath)) {
            log_message('error', "[TebingKanan] ❌ File Excel tidak ditemukan: {$filePath}");
            return false;
        }

        $ambangData = getAmbangTebingKanan($filePath, 'AMBANG TIAP CM');
        $ambang = cariAmbangTebingKanan($tma, $ambangData);

        if ($ambang === null) {
            log_message('error', "[TebingKanan] ❌ Ambang tidak ditemukan untuk TMA {$tma}");
            return false;
        }

        // 🔹 4) Ambil nilai B5 dari tabel Thomson Weir
        $thomsonRow = $db->table('p_thomson_weir')
            ->select('B5')
            ->where('pengukuran_id', $pengukuranId)
            ->get()
            ->getRowArray();

        $B5 = (float)($thomsonRow['B5'] ?? 0);

        // 🔹 5) Simpan / update data ke p_tebingkanan
        $hasil = [
            'pengukuran_id' => $pengukuranId,
            'sr'            => $srTotal,
            'ambang'        => $ambang,
            'B5'            => $B5,
        ];

        // insert/update tetap dijalankan meski SR gagal
        $exists = $this->model->where('pengukuran_id', $pengukuranId)->first();

        if ($exists) {
            $this->model->update($exists['id'], $hasil);
            log_message('debug', "[TebingKanan] 🔄 UPDATE untuk ID {$pengukuranId} (SR: {$srTotal}, B5: {$B5})");
        } else {
            $this->model->insert($hasil);
            log_message('debug', "[TebingKanan] 🆕 INSERT untuk ID {$pengukuranId} (SR: {$srTotal}, B5: {$B5})");
        }

        log_message('debug', "[TebingKanan] ✅ Selesai proses ID={$pengukuranId} | SR={$srTotal}, Ambang={$ambang}, B5={$B5}");

        return $hasil;
    }
}
