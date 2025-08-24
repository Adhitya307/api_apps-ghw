<?php

namespace App\Controllers\Rembesan;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Rembesan\PerhitunganBatasMaksimalModel;

class BatasMaksimalController extends BaseController
{
    protected $batasData = [];
    protected $modelBatas;

    public function __construct()
    {
        helper('Rembesan/BatasMaksimalHelper');
        $this->modelBatas = new PerhitunganBatasMaksimalModel();
    }

    /**
     * Load data batas maksimal dari file Excel
     *
     * @param string|null $filePath
     * @return array ['TMA' => 'batas maksimal']
     */
    public function loadFromExcel($filePath = null)
    {
        if (!$filePath) {
            $filePath = FCPATH . 'assets/excel/tabel_ambang.xlsx';
        }

        if (!file_exists($filePath)) {
            log_message('error', "[BatasMaksimal] File Excel tidak ditemukan: $filePath");
            return [];
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $this->batasData = loadBatasMaksimal($sheet);
        } catch (\Exception $e) {
            log_message('error', "[BatasMaksimal] Gagal load Excel: " . $e->getMessage());
            return [];
        }

        return $this->batasData;
    }

    /**
     * Cari batas maksimal untuk TMA tertentu
     *
     * @param float $tma
     * @return float|null
     */
    public function getBatasMaksimal($tma)
    {
        if (empty($this->batasData)) {
            return null;
        }

        return cariBatasMaksimal($tma, $this->batasData);
    }

    /**
     * Contoh testing via browser
     *
     * @param float $tma
     */
    public function test($tma = 0)
    {
        $this->loadFromExcel(); // otomatis ambil dari path default

        $batas = $this->getBatasMaksimal($tma);

        echo "<h3>Hasil Batas Maksimal untuk TMA = $tma</h3>";
        echo "<pre>";
        print_r($batas);
        echo "</pre>";
    }

    /**
     * Digunakan oleh RumusRembesanController
     * Hitung & simpan batas maksimal ke DB
     *
     * @param int $pengukuran_id
     * @return array|null ['tma' => float, 'batas' => float]
     */
    public function getBatasInternal($pengukuran_id)
    {
        $db = \Config\Database::connect();
        $query = $db->table('t_data_pengukuran')
                    ->select('id, tma_waduk')
                    ->where('id', $pengukuran_id)
                    ->get()
                    ->getRowArray();

        if (!$query || !isset($query['tma_waduk'])) {
            log_message('debug', "[BatasMaksimal] TMA untuk pengukuran_id={$pengukuran_id} tidak ditemukan");
            return null;
        }

        $tmaWaduk = (float) $query['tma_waduk'];

        if (empty($this->batasData)) {
            $this->loadFromExcel(); // load Excel jika belum ada
        }

        $batas = cariBatasMaksimal($tmaWaduk, $this->batasData);

        // Simpan ke database (insert atau update)
        $dataInsert = [
            'pengukuran_id' => $pengukuran_id,
            'batas_maksimal' => $batas
        ];

        $existing = $this->modelBatas->getByPengukuranId($pengukuran_id);

        if ($existing) {
            $this->modelBatas->updateBatasMaksimal($existing['id'], $dataInsert);
            log_message('debug', "[BatasMaksimal] Update DB batas maksimal untuk ID: $pengukuran_id");
        } else {
            $this->modelBatas->insertBatasMaksimal($dataInsert);
            log_message('debug', "[BatasMaksimal] Insert DB batas maksimal untuk ID: $pengukuran_id");
        }

        return [
            'tma'   => $tmaWaduk,
            'batas' => $batas
        ];
    }
}
