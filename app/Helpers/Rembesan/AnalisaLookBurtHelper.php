<?php

namespace App\Helpers\Rembesan;

use App\Models\Rembesan\PerhitunganIntiGaleryModel;
use App\Models\Rembesan\MDataPengukuran; // model t_data_pengukuran
use PhpOffice\PhpSpreadsheet\IOFactory;

class AnalisaLookBurtHelper
{
    protected $intiGaleryModel;
    protected $pengukuranModel;

    public function __construct()
    {
        $this->intiGaleryModel = new PerhitunganIntiGaleryModel();
        $this->pengukuranModel = new MDataPengukuran();
    }

    /**
     * Hitung Analisa Look Burt
     *
     * @param int $pengukuran_id
     * @return array|null
     */
    public function hitungLookBurt($pengukuran_id)
    {
        // 🔹 Ambil data inti galery
        $dataInti = $this->intiGaleryModel
            ->where('pengukuran_id', $pengukuran_id)
            ->first();

        // 🔹 Ambil data pengukuran (tma_waduk)
        $dataPengukuran = $this->pengukuranModel
            ->where('id', $pengukuran_id)
            ->first();

        if (!$dataInti || !$dataPengukuran) {
            return null;
        }

        // 🔹 Ambil rembesan bendungan (a1)
        $rembesan_bendungan = (float) ($dataInti['a1'] ?? 0);

        // 🔹 Ambil TMA Waduk
        $tma_waduk = (float) ($dataPengukuran['tma_waduk'] ?? 0);

        // 🔹 Cari panjang bendungan berdasarkan TMA Waduk (Excel)
        $panjang_bendungan = $this->lookupPanjangBendungan($tma_waduk);

        // 🔹 Hitung rembesan per m
        $rembesan_per_m = ($panjang_bendungan > 0)
            ? $rembesan_bendungan / $panjang_bendungan
            : 0;

        // 🔹 Nilai ambang (fixed / permanen)
        $nilai_ambang_ok    = 0.28;
        $nilai_ambang_notok = 0.56;

        // 🔹 Tentukan keterangan sesuai rumus Excel
        if ($rembesan_per_m < $nilai_ambang_ok) {
            $keterangan = "aman";
        } elseif ($rembesan_per_m <= $nilai_ambang_notok) {
            $keterangan = "peringatan";
        } else {
            $keterangan = "bahaya";
        }

        return [
            'pengukuran_id'      => $pengukuran_id,
            'tma_waduk'          => $tma_waduk,
            'rembesan_bendungan' => $rembesan_bendungan,
            'panjang_bendungan'  => $panjang_bendungan,
            'rembesan_per_m'     => $rembesan_per_m,
            'nilai_ambang_ok'    => $nilai_ambang_ok,
            'nilai_ambang_notok' => $nilai_ambang_notok,
            'keterangan'         => $keterangan,
        ];
    }

    /**
     * Lookup Panjang Bendungan dari Excel berdasarkan TMA Waduk
     *
     * @param float $tma_waduk
     * @return float
     * @throws \Exception
     */
    protected function lookupPanjangBendungan($tma_waduk)
    {
        $filePath = FCPATH . 'assets/excel/Panjang_Bendungan.xlsx';

        if (!file_exists($filePath)) {
            throw new \Exception("File Excel tidak ditemukan: " . $filePath);
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('Panjang Bendungan Tiap Cm');

        if (!$sheet) {
            throw new \Exception("Sheet 'Panjang Bendungan Tiap Cm' tidak ditemukan");
        }

        // Ambil range sesuai rumus Excel: B5:C2005
        $data = $sheet->rangeToArray('B5:C2005', null, true, true, true);

        foreach ($data as $row) {
            $key   = (float) $row['B']; // nilai lookup = tma_waduk
            $value = (float) $row['C']; // hasil panjang bendungan
            if ($key === (float) $tma_waduk) {
                return $value;
            }
        }

        // Jika tidak ketemu, kembalikan 0
        return 0;
    }
}
