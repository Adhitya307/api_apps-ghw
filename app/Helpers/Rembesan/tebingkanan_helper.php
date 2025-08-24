<?php
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!function_exists('hitungSrTebingKanan')) {
    /**
     * Hitung total debit SR Tebing Kanan dari data SR.
     * Prioritas ambil langsung nilai q dari dataSR (sr_xxx_q).
     */
    function hitungSrTebingKanan(array $dataSr, array $srFields): float
    {
        $total = 0.0;
        foreach ($srFields as $field) {
            // Ambil nilai q langsung dari hasil perhitungan SR jika ada
            $q = (float)($dataSr["sr_{$field}_q"] ?? 0);

            if ($q == 0.0) {
                // fallback hitung manual jika q tidak tersedia
                $nilai = (float)($dataSr["sr_{$field}_nilai"] ?? 0);
                $kode  = (string)($dataSr["sr_{$field}_kode"] ?? '');
                $q     = perhitunganQ_sr($nilai, $kode);
            }

            $total += $q;
        }
        return $total;
    }
}

if (!function_exists('getAmbangTebingKanan')) {
    /**
     * Load data ambang Tebing Kanan dari Excel.
     * Kolom B = TMA, Kolom E = Ambang
     * 
     * @return array ['644.82' => 123.45, ...]
     */
    function getAmbangTebingKanan(string $fileExcel, string $sheetName = 'AMBANG TIAP CM'): array
    {
        $spreadsheet = IOFactory::load($fileExcel);
        $sheet = $spreadsheet->getSheetByName($sheetName) ?: $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $data = [];

        for ($row = 5; $row <= $highestRow; $row++) {
            $tma    = $sheet->getCell('B' . $row)->getCalculatedValue();
            $ambang = $sheet->getCell('E' . $row)->getCalculatedValue();

            if ($tma === null || $tma === '') {
                continue;
            }

            $tmaKey = number_format((float)$tma, 2, '.', '');
            $data[$tmaKey] = (float)$ambang;
        }

        return $data;
    }
}

if (!function_exists('cariAmbangTebingKanan')) {
    /**
     * Cari ambang berdasarkan TMA.
     * Jika tidak ada match persis, pilih nilai ambang terdekat.
     */
    function cariAmbangTebingKanan(float $tma, array $ambangData)
    {
        if (empty($ambangData)) {
            return null;
        }

        $tmaKey = number_format($tma, 2, '.', '');

        // Exact match
        if (isset($ambangData[$tmaKey])) {
            return $ambangData[$tmaKey];
        }

        // Cari nilai TMA terdekat
        $closestKey = null;
        $closestDiff = INF;
        foreach ($ambangData as $key => $val) {
            $diff = abs((float)$tmaKey - (float)$key);
            if ($diff < $closestDiff) {
                $closestDiff = $diff;
                $closestKey  = $key;
            }
        }

        return $closestKey !== null ? $ambangData[$closestKey] : null;
    }
}
