<?php
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!function_exists('hitungIntiGalery')) {
    /**
     * Hitung Inti Galery:
     * - a1 = a1_r + a1_l (diambil dari tabel p_thomson_weir)
     * - ambang_a1 = lookup dari Excel (key = TMA Waduk / tma_waduk)
     *
     * @param int $pengukuranId
     * @return array|false
     */
    function hitungIntiGalery(int $pengukuranId)
    {
        $db = \Config\Database::connect();

        // 1) Ambil data pengukuran untuk TMA (lookup Excel)
        $dataPengukuran = $db->table('t_data_pengukuran')
            ->select('id, tma_waduk')
            ->where('id', $pengukuranId)
            ->get();

        if (!$dataPengukuran) {
            log_message('error', "[IntiGalery] ❌ Query data pengukuran gagal untuk ID {$pengukuranId}");
            return false;
        }

        $dataPengukuran = $dataPengukuran->getRowArray();
        if (!$dataPengukuran) {
            log_message('error', "[IntiGalery] ❌ Data pengukuran tidak ditemukan untuk ID {$pengukuranId}");
            return false;
        }

        $tma = (float) ($dataPengukuran['tma_waduk'] ?? 0);

        // 2) Ambil hasil Thomson (a1_r dan a1_l) dari p_thomson_weir
        //    NOTE: jika nama tabel/kolom berbeda, sesuaikan di sini.
        $dataThomson = $db->table('p_thomson_weir')
            ->select('a1_r, a1_l')
            ->where('pengukuran_id', $pengukuranId)
            ->get();

        if (!$dataThomson) {
            log_message('error', "[IntiGalery] ❌ Query Thomson gagal untuk pengukuran_id={$pengukuranId}");
            return false;
        }

        $dataThomson = $dataThomson->getRowArray();
        if (!$dataThomson) {
            log_message('error', "[IntiGalery] ❌ Data Thomson tidak ditemukan untuk pengukuran_id={$pengukuranId}");
            return false;
        }

        $a1_r = (float) ($dataThomson['a1_r'] ?? 0);
        $a1_l = (float) ($dataThomson['a1_l'] ?? 0);
        $a1   = $a1_r + $a1_l;

        // 3) Baca Excel referensi ambang (tabel_ambang.xlsx) dari public/assets/excel
        $filePath = FCPATH . 'assets/excel/tabel_ambang.xlsx';
        if (!is_file($filePath)) {
            log_message('error', "[IntiGalery] ❌ File referensi tidak ditemukan di {$filePath}");
            return false;
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet       = $spreadsheet->getSheet(0);
        } catch (\Throwable $e) {
            log_message('error', "[IntiGalery] ❌ Gagal membaca Excel: " . $e->getMessage());
            return false;
        }

        // 4) Ambil data ambang dari helper lain
        if (!function_exists('getAmbangData')) {
            // Pastikan helper ini berisi getAmbangData() dan cariAmbangArray()
            helper('rembesan/rumusrembesan');
        }

        $ambangData = getAmbangData($sheet);
        $ambang     = cariAmbangArray($tma, $ambangData);

        if ($ambang === null) {
            log_message('error', "[IntiGalery] ❌ Ambang tidak ditemukan untuk TMA {$tma}");
            return false;
        }

        // 5) Kembalikan hasil (controller yang akan insert/update ke DB)
        $hasil = [
            'pengukuran_id' => $pengukuranId,
            'a1'            => $a1,       // hasil dari a1_r + a1_l
            'ambang_a1'     => $ambang,  // dari Excel lookup dengan kunci TMA
        ];

        log_message(
            'debug',
            "[IntiGalery] ✅ Hitung selesai untuk ID {$pengukuranId} | TMA={$tma}, a1_r={$a1_r}, a1_l={$a1_l}, a1={$a1}, ambang={$ambang}"
        );

        return $hasil;
    }
}
