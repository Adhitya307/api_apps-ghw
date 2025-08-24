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

        // 🔹 1) Ambil data pengukuran
        $pengukuran = $db->table('t_data_pengukuran')
            ->select('id, tma_waduk')
            ->where('id', $pengukuranId)
            ->get()
            ->getRowArray();

        if (!$pengukuran) {
            log_message('error', "[TebingKanan] ❌ Data pengukuran tidak ditemukan untuk ID {$pengukuranId}");
            return false;
        }
        $tma = (float) $pengukuran['tma_waduk'];

        // 🔹 2) Ambil data SR hasil perhitungan
        $dataSr = $db->table('p_sr')
            ->where('pengukuran_id', $pengukuranId)
            ->get()
            ->getRowArray();

        if (!$dataSr) {
            log_message('error', "[TebingKanan] ❌ Data SR tidak ditemukan untuk pengukuran_id={$pengukuranId}");
            return false;
        }

        // 🔹 3) Daftar SR yang dipakai khusus Tebing Kanan
        $srFields = [
            1,40,66,67,68,69,70,71,72,73,74,75,76,77,78,
            79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,
            94,95,96,97,98,99,100,101,102,103,104,105,106
        ];

        // 🔹 4) Hitung total SR Tebing Kanan
        $srTotal = hitungSrTebingKanan($dataSr, $srFields);

        // 🔹 5) Ambil ambang dari Excel
        $filePath = FCPATH . 'assets/excel/tabel_ambang.xlsx';
        if (!is_file($filePath)) {
            log_message('error', "[TebingKanan] ❌ File Excel tidak ditemukan di {$filePath}");
            return false;
        }

        $ambangData = getAmbangTebingKanan($filePath, 'AMBANG TIAP CM');
        $ambang     = cariAmbangTebingKanan($tma, $ambangData);

        if ($ambang === null) {
            log_message('error', "[TebingKanan] ❌ Ambang Tebing Kanan tidak ditemukan untuk TMA {$tma}");
            return false;
        }

        // 🔹 6) Ambil nilai B5 dari tabel p_thomson_weir
        $thomsonRow = $db->table('p_thomson_weir')
            ->select('B5')
            ->where('pengukuran_id', $pengukuranId)
            ->get()
            ->getRowArray();

        $B5 = (float) ($thomsonRow['B5'] ?? 0);

        // 🔹 7) Simpan hasil ke tabel p_tebingkanan
        $hasil = [
            'pengukuran_id' => $pengukuranId,
            'sr'            => $srTotal,
            'ambang'        => $ambang,
            'B5'            => $B5,
        ];

        $exists = $this->model->where('pengukuran_id', $pengukuranId)->first();
        if ($exists) {
            $this->model->update($exists['id'], $hasil);
            log_message('debug', "[TebingKanan] 🔄 Update DB untuk ID {$pengukuranId}");
        } else {
            $this->model->insert($hasil);
            log_message('debug', "[TebingKanan] ✅ Insert DB untuk ID {$pengukuranId}");
        }

        log_message('debug', "[TebingKanan] ✅ Selesai | ID={$pengukuranId}, sr={$srTotal}, ambang={$ambang}, B5={$B5}");

        return $hasil;
    }
}
