<?php

namespace App\Controllers\Rightpiezo;

use CodeIgniter\Controller;
use Config\Database;
use App\Models\Rightpiezo\T_pengukuran_rightpiez;
use App\Models\Rightpiezo\T_pembacaan;
use App\Models\Rightpiezo\B_piezo_metrik;
use App\Models\Rightpiezo\I_reading_atas;
use App\Models\Rightpiezo\Perhitungan_tengah;
use App\Models\Rightpiezo\Elevasi_dasar;

class Inputdatarightpiez extends Controller
{
    protected $db;
    protected $pengukuranModel;
    protected $pembacaanModel;
    protected $metrikModel;
    protected $ireadingAtasModel;
    protected $perhitunganTengahModel;
    protected $elevasiDasarModel;

    // Daftar lokasi untuk Right Piezo
    protected $lokasiList = [
        'R-01', 'R-02', 'R-03', 'R-04', 'R-05', 'R-06', 
        'R-07', 'R-08', 'R-09', 'R-10', 'R-11', 'R-12', 
        'IPZ-01', 'PZ-04'
    ];

    public function __construct()
    {
        // Konfigurasi database db_right_piez
        $this->db = Database::connect('db_right_piez');
        
        $this->pengukuranModel = new T_pengukuran_rightpiez();
        $this->pembacaanModel = new T_pembacaan();
        $this->metrikModel = new B_piezo_metrik();
        $this->ireadingAtasModel = new I_reading_atas();
        $this->perhitunganTengahModel = new Perhitungan_tengah();
        $this->elevasiDasarModel = new Elevasi_dasar();

        // Support CORS untuk testing Android / Postman
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        
        // Handle preflight request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit;
        }
    }

    private function getVal($key, $data)
    {
        return isset($data[$key]) && trim($data[$key]) !== '' ? $data[$key] : null;
    }

    public function index()
    {
        try {
            // Log raw input untuk debugging
            $rawInput = file_get_contents('php://input');
            log_message('debug', '[Inputdatarightpiez] Raw input: ' . $rawInput);
            
            $data = json_decode($rawInput, true);
            
            if (!$data || json_last_error() !== JSON_ERROR_NONE) {
                $data = $this->request->getPost();
            }

            if (!$data) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Tidak ada data dikirim!"
                ]);
            }

            $mode = $this->getVal("mode", $data);
            $pengukuran_id = $this->getVal("pengukuran_id", $data);
            $temp_id = $this->getVal("temp_id", $data);

            log_message('debug', "[Inputdatarightpiez] mode={$mode}, pengukuran_id={$pengukuran_id}, temp_id={$temp_id}");

            if (!$mode) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter mode wajib dikirim!"
                ]);
            }

            // Mode update_tma sebelum mode pengukuran
            if ($mode === "update_tma") {
                return $this->updateTMAPengukuran($data, $pengukuran_id);
            }

            if ($mode === "pengukuran") {
                return $this->savePengukuran($data, $temp_id);
            }

            // Handle pencarian pengukuran_id dari temp_id
            if ((!$pengukuran_id || !is_numeric($pengukuran_id)) && $temp_id) {
                $pengukuran_id = $this->findPengukuranIdByTempId($temp_id);
                if (!$pengukuran_id) {
                    return $this->response->setJSON([
                        "status" => "error",
                        "message" => "Tidak ditemukan pengukuran dari temp_id: " . $temp_id
                    ]);
                }
            }

            if (!$pengukuran_id || !is_numeric($pengukuran_id)) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "pengukuran_id wajib dikirim atau ditemukan dari temp_id"
                ]);
            }

            $cekPengukuran = $this->pengukuranModel->find($pengukuran_id);
            if (!$cekPengukuran) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data pengukuran dengan ID $pengukuran_id tidak ditemukan!"
                ]);
            }

            // Handle mode pembacaan untuk semua lokasi
            if (strpos($mode, "pembacaan_") === 0) {
                $lokasi = strtoupper(str_replace("pembacaan_", "", $mode));
                return $this->savePembacaan($data, $pengukuran_id, $lokasi);
            }

            // Handle mode metrik
            if ($mode === "metrik") {
                return $this->saveMetrik($data, $pengukuran_id);
            }

            // Handle mode ireading_atas
            if ($mode === "ireading_atas") {
                return $this->saveIreadingAtas($data, $pengukuran_id);
            }

            // Handle mode perhitungan_tengah
            if ($mode === "perhitungan_tengah") {
                return $this->savePerhitunganTengah($data, $pengukuran_id);
            }

            // Handle mode elevasi_dasar
            if ($mode === "elevasi_dasar") {
                return $this->saveElevasiDasar($data, $pengukuran_id);
            }

            return $this->response->setJSON([
                "status" => "error",
                "message" => "Mode tidak dikenali: $mode"
            ]);

        } catch (\Exception $e) {
            log_message('error', '[Inputdatarightpiez] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan server: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mencari pengukuran_id berdasarkan temp_id
     */
    private function findPengukuranIdByTempId($temp_id)
    {
        $row = $this->db->table("t_pengukuran_rightpiez")
            ->select("id_pengukuran")
            ->where("temp_id", $temp_id)
            ->get()
            ->getRow();
        
        if ($row) {
            $pengukuran_id = $row->id_pengukuran;
            log_message('debug', "[Inputdatarightpiez] pengukuran_id ditemukan dari temp_id={$temp_id} → id={$pengukuran_id}");
            return $pengukuran_id;
        }
        
        return null;
    }

private function savePengukuran($data, $temp_id)
{
    try {
        $pengukuran_id = $this->getVal('pengukuran_id', $data);
        $tahun   = $this->getVal('tahun', $data);
        $periode = $this->getVal('periode', $data);
        $tanggal = $this->getVal('tanggal', $data);
        $tma     = $this->getVal('tma', $data);
        $ch_hujan = $this->getVal('ch_hujan', $data);

        if (!$tahun || !$tanggal) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Tahun dan Tanggal wajib diisi!"
            ]);
        }

        if ($periode && !preg_match('/^TW-/i', $periode) && is_numeric($periode)) {
            $periode = "TW-" . $periode;
        }

        // CREATE
        if (!$pengukuran_id || !is_numeric($pengukuran_id)) {
            // Cek existing berdasarkan tahun, periode, tanggal
            $checkExisting = $this->db->table("t_pengukuran_rightpiez")
                ->where("tahun", $tahun)
                ->where("periode", $periode)
                ->where("tanggal", $tanggal)
                ->get()
                ->getRow();

            if ($checkExisting) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data pengukuran dengan Tahun $tahun, Periode $periode, dan Tanggal $tanggal sudah ada!",
                    "pengukuran_id" => $checkExisting->id_pengukuran,
                    "temp_id" => $checkExisting->temp_id
                ]);
            }

            // Generate temp_id jika tidak ada dari Android
            if (!$temp_id) {
                $temp_id = "RPZ_" . date('Ymd_His') . "_" . rand(1000, 9999);
            }

            $insertData = [
                "tahun" => $tahun,
                "periode" => $periode,
                "tanggal" => $tanggal,
                "tma" => $tma,
                "ch_hujan" => $ch_hujan,
                "temp_id" => $temp_id
            ];
            
            $this->pengukuranModel->save($insertData);
            $pengukuran_id = $this->pengukuranModel->getInsertID();

            // ✅ INSERT METRIK DEFAULT
            $this->metrikModel->save([
                "id_pengukuran" => $pengukuran_id,
                "feet" => 0.3048,
                "inch" => 0.0254
            ]);

            // ✅ ✅ ✅ INSERT DATA I-READING ATAS DEFAULT OTOMATIS
            $this->insertDefaultIreadingAtas($pengukuran_id);

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Data pengukuran berhasil dibuat.",
                "pengukuran_id" => $pengukuran_id,
                "temp_id" => $temp_id
            ]);
        }

        // UPDATE (kode yang sudah ada...)
        $updateData = [];
        if ($tma !== null) $updateData["tma"] = $tma;
        if ($ch_hujan !== null) $updateData["ch_hujan"] = $ch_hujan;
        if ($periode !== null) $updateData["periode"] = $periode;
        if ($temp_id !== null) $updateData["temp_id"] = $temp_id;
        
        if (!empty($updateData)) {
            $this->pengukuranModel->update($pengukuran_id, $updateData);
        }

        return $this->response->setJSON([
            "status" => "success",
            "message" => "Data pengukuran berhasil diperbarui.",
            "pengukuran_id" => $pengukuran_id,
            "temp_id" => $temp_id
        ]);

    } catch (\Exception $e) {
        return $this->response->setJSON([
            "status" => "error",
            "message" => "Terjadi kesalahan saat menyimpan data pengukuran: " . $e->getMessage()
        ]);
    }
}

/**
 * ✅ METHOD BARU: Insert data default I-reading atas
 */
private function insertDefaultIreadingAtas($pengukuran_id)
{
    try {
        // DATA DEFAULT BERDASARKAN EXCEL ANDA
        $defaultData = [
            'R-01' => ['elv_piez' => 651.48, 'kedalaman' => 50.00],
            'R-02' => ['elv_piez' => 647.22, 'kedalaman' => 60.00],
            'R-03' => ['elv_piez' => 606.43, 'kedalaman' => 50.00],
            'R-04' => ['elv_piez' => 586.41, 'kedalaman' => 51.00],
            'R-05' => ['elv_piez' => 655.30, 'kedalaman' => 50.27],
            'R-06' => ['elv_piez' => 661.03, 'kedalaman' => 60.00],
            'R-07' => ['elv_piez' => 649.06, 'kedalaman' => 50.00],
            'R-08' => ['elv_piez' => 671.51, 'kedalaman' => 40.00],
            'R-09' => ['elv_piez' => 656.48, 'kedalaman' => 42.00],
            'R-10' => ['elv_piez' => 677.35, 'kedalaman' => null], // "-" di Excel
            'R-11' => ['elv_piez' => 644.90, 'kedalaman' => 57.00],
            'R-12' => ['elv_piez' => 630.49, 'kedalaman' => 42.00],
            'IPZ-01' => ['elv_piez' => 649.90, 'kedalaman' => null], // "-" di Excel
            'PZ-04' => ['elv_piez' => 651.39, 'kedalaman' => 73.50]
        ];

        $batchData = [];
        foreach ($defaultData as $titik => $data) {
            $batchData[] = [
                'id_pengukuran' => $pengukuran_id,
                'titik_piezometer' => $titik,
                'Elv_Piez' => $data['elv_piez'],
                'kedalaman' => $data['kedalaman']
            ];
        }

        // Insert batch ke database
        $result = $this->ireadingAtasModel->insertBatch($batchData);

        log_message('debug', "[insertDefaultIreadingAtas] Data default berhasil dimasukkan untuk pengukuran_id: $pengukuran_id");

        return $result;

    } catch (\Exception $e) {
        log_message('error', "[insertDefaultIreadingAtas] Error: " . $e->getMessage());
        return false;
    }
}

    /**
     * Method untuk update TMA saja
     */
    private function updateTMAPengukuran($data, $pengukuran_id)
    {
        try {
            $tma = $this->getVal('tma', $data);
            $ch_hujan = $this->getVal('ch_hujan', $data);
            
            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "pengukuran_id diperlukan untuk update TMA!"
                ]);
            }

            // Cek apakah data pengukuran ada
            $existing = $this->db->table("t_pengukuran_rightpiez")
                ->where("id_pengukuran", $pengukuran_id)
                ->get()
                ->getRow();

            if (!$existing) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data pengukuran dengan ID $pengukuran_id tidak ditemukan!"
                ]);
            }

            // Update TMA dan CH Hujan
            $updateData = [];
            if ($tma !== null) $updateData["tma"] = $tma;
            if ($ch_hujan !== null) $updateData["ch_hujan"] = $ch_hujan;

            if (!empty($updateData)) {
                $this->db->table("t_pengukuran_rightpiez")
                    ->where("id_pengukuran", $pengukuran_id)
                    ->update($updateData);
            }

            log_message('debug', "[updateTMAPengukuran] Data updated for ID: $pengukuran_id, TMA: $tma, CH Hujan: $ch_hujan");

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Data TMA berhasil diperbarui.",
                "pengukuran_id" => $pengukuran_id
            ]);

        } catch (\Exception $e) {
            log_message('error', '[updateTMAPengukuran] Exception: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat update TMA: " . $e->getMessage()
            ]);
        }
    }

    private function savePembacaan($data, $pengukuran_id, $lokasi)
    {
        try {
            // Validasi lokasi
            if (!in_array($lokasi, $this->lokasiList)) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Lokasi tidak valid: $lokasi"
                ]);
            }

            // Ambil input user untuk feet/inch
            $feet = $this->getVal('feet', $data);
            $inch = $this->getVal('inch', $data);

            // Cek apakah data sudah ada
            $existing = $this->pembacaanModel
                ->where("id_pengukuran", $pengukuran_id)
                ->where("lokasi", $lokasi)
                ->first();

            if ($existing) {
                // Update data yang sudah ada
                $updateData = [];
                if ($feet !== null) $updateData["feet"] = $feet;
                if ($inch !== null) $updateData["inch"] = $inch;

                if (!empty($updateData)) {
                    $this->pembacaanModel
                        ->where("id_pengukuran", $pengukuran_id)
                        ->where("lokasi", $lokasi)
                        ->set($updateData)
                        ->update();

                    return $this->response->setJSON([
                        "status" => "success",
                        "message" => "Data pembacaan $lokasi berhasil diperbarui."
                    ]);
                } else {
                    return $this->response->setJSON([
                        "status" => "error",
                        "message" => "Tidak ada data yang diupdate untuk pembacaan $lokasi"
                    ]);
                }
            }

            // Insert data baru
            $insertData = [
                "id_pengukuran" => $pengukuran_id, 
                "lokasi" => $lokasi
            ];

            if ($feet !== null) $insertData["feet"] = $feet;
            if ($inch !== null) $insertData["inch"] = $inch;

            $this->pembacaanModel->save($insertData);

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Data pembacaan $lokasi berhasil disimpan."
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat menyimpan data pembacaan $lokasi: " . $e->getMessage()
            ]);
        }
    }

    private function saveMetrik($data, $pengukuran_id)
    {
        try {
            $updateData = ['id_pengukuran' => $pengukuran_id];
            
            // Ambil data untuk semua lokasi
            foreach ($this->lokasiList as $lokasi) {
                $value = $this->getVal($lokasi, $data);
                if ($value !== null) {
                    $updateData[$lokasi] = $value;
                }
            }

            // Cek apakah data sudah ada
            $existing = $this->metrikModel->find($pengukuran_id);
            
            if ($existing) {
                $this->metrikModel->update($pengukuran_id, $updateData);
                $message = "Data metrik berhasil diperbarui.";
            } else {
                $this->metrikModel->save($updateData);
                $message = "Data metrik berhasil disimpan.";
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => $message
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat menyimpan data metrik: " . $e->getMessage()
            ]);
        }
    }

    private function saveIreadingAtas($data, $pengukuran_id)
    {
        try {
            $updateData = ['id_pengukuran' => $pengukuran_id];
            
            // Ambil data untuk semua lokasi
            foreach ($this->lokasiList as $lokasi) {
                $elv_piez = $this->getVal($lokasi . '_elv_piez', $data);
                $kedalaman = $this->getVal($lokasi . '_kedalaman', $data);
                $data_value = $this->getVal($lokasi . '_data', $data);
                
                if ($elv_piez !== null) $updateData[$lokasi . '_elv_piez'] = $elv_piez;
                if ($kedalaman !== null) $updateData[$lokasi . '_kedalaman'] = $kedalaman;
                if ($data_value !== null) $updateData[$lokasi . '_data'] = $data_value;
            }

            // Cek apakah data sudah ada
            $existing = $this->ireadingAtasModel->find($pengukuran_id);
            
            if ($existing) {
                $this->ireadingAtasModel->update($pengukuran_id, $updateData);
                $message = "Data I-reading atas berhasil diperbarui.";
            } else {
                $this->ireadingAtasModel->save($updateData);
                $message = "Data I-reading atas berhasil disimpan.";
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => $message
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat menyimpan data I-reading atas: " . $e->getMessage()
            ]);
        }
    }

    private function savePerhitunganTengah($data, $pengukuran_id)
    {
        try {
            $updateData = ['id_pengukuran' => $pengukuran_id];
            
            // Ambil data untuk semua lokasi
            foreach ($this->lokasiList as $lokasi) {
                $elv_piez = $this->getVal($lokasi . '_elv_piez', $data);
                $kedalaman = $this->getVal($lokasi . '_kedalaman', $data);
                $data_value = $this->getVal($lokasi . '_data', $data);
                
                if ($elv_piez !== null) $updateData[$lokasi . '_elv_piez'] = $elv_piez;
                if ($kedalaman !== null) $updateData[$lokasi . '_kedalaman'] = $kedalaman;
                if ($data_value !== null) $updateData[$lokasi . '_data'] = $data_value;
            }

            // Cek apakah data sudah ada
            $existing = $this->perhitunganTengahModel->find($pengukuran_id);
            
            if ($existing) {
                $this->perhitunganTengahModel->update($pengukuran_id, $updateData);
                $message = "Data perhitungan tengah berhasil diperbarui.";
            } else {
                $this->perhitunganTengahModel->save($updateData);
                $message = "Data perhitungan tengah berhasil disimpan.";
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => $message
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat menyimpan data perhitungan tengah: " . $e->getMessage()
            ]);
        }
    }

    private function saveElevasiDasar($data, $pengukuran_id)
    {
        try {
            $updateData = ['id_pengukuran' => $pengukuran_id];
            
            // Ambil data untuk semua lokasi
            foreach ($this->lokasiList as $lokasi) {
                $elv_piez = $this->getVal($lokasi . '_elv_piez', $data);
                $kedalaman = $this->getVal($lokasi . '_kedalaman', $data);
                $data_value = $this->getVal($lokasi . '_data', $data);
                
                if ($elv_piez !== null) $updateData[$lokasi . '_elv_piez'] = $elv_piez;
                if ($kedalaman !== null) $updateData[$lokasi . '_kedalaman'] = $kedalaman;
                if ($data_value !== null) $updateData[$lokasi . '_data'] = $data_value;
            }

            // Cek apakah data sudah ada
            $existing = $this->elevasiDasarModel->find($pengukuran_id);
            
            if ($existing) {
                $this->elevasiDasarModel->update($pengukuran_id, $updateData);
                $message = "Data elevasi dasar berhasil diperbarui.";
            } else {
                $this->elevasiDasarModel->save($updateData);
                $message = "Data elevasi dasar berhasil disimpan.";
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => $message
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat menyimpan data elevasi dasar: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data pengukuran (API untuk Android)
     */
    public function getPengukuran()
    {
        try {
            $pengukuran = $this->db->table('t_pengukuran_rightpiez')
                ->select('id_pengukuran, tahun, periode, tanggal, tma, ch_hujan, temp_id')
                ->orderBy('tanggal', 'DESC')
                ->orderBy('tahun', 'DESC')
                ->orderBy('periode', 'DESC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                "status" => "success",
                "data" => $pengukuran
            ]);

        } catch (\Exception $e) {
            log_message('error', '[getPengukuran] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data pengukuran: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data berdasarkan temp_id
     */
    public function getPengukuranByTempId()
    {
        try {
            $temp_id = $this->request->getGet('temp_id');

            if (!$temp_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter temp_id diperlukan!"
                ]);
            }

            $pengukuran = $this->db->table('t_pengukuran_rightpiez')
                ->select('id_pengukuran, tahun, periode, tanggal, tma, ch_hujan, temp_id')
                ->where('temp_id', $temp_id)
                ->get()
                ->getRowArray();

            if (!$pengukuran) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data pengukuran dengan temp_id $temp_id tidak ditemukan!"
                ]);
            }

            return $this->response->setJSON([
                "status" => "success",
                "data" => $pengukuran
            ]);

        } catch (\Exception $e) {
            log_message('error', '[getPengukuranByTempId] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data pengukuran: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data pembacaan berdasarkan pengukuran_id dan lokasi
     */
    public function getData()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');
            $lokasi = $this->request->getGet('lokasi'); // R-01, R-02, ..., PZ-04

            if (!$pengukuran_id || !$lokasi) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id dan lokasi diperlukan!"
                ]);
            }

            // Validasi lokasi
            $lokasi = strtoupper($lokasi);
            if (!in_array($lokasi, $this->lokasiList)) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Lokasi tidak valid: $lokasi"
                ]);
            }

            $data = $this->pembacaanModel
                ->where('id_pengukuran', $pengukuran_id)
                ->where('lokasi', $lokasi)
                ->first();

            return $this->response->setJSON([
                "status" => "success",
                "data" => $data ?: []
            ]);

        } catch (\Exception $e) {
            log_message('error', '[getData] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan semua data pembacaan berdasarkan pengukuran_id
     */
    public function getAllData()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id diperlukan!"
                ]);
            }

            $allData = $this->pembacaanModel
                ->where('id_pengukuran', $pengukuran_id)
                ->findAll();

            // Format data per lokasi
            $formattedData = [];
            foreach ($allData as $data) {
                $formattedData[$data['lokasi']] = $data;
            }

            return $this->response->setJSON([
                "status" => "success",
                "data" => $formattedData
            ]);

        } catch (\Exception $e) {
            log_message('error', '[getAllData] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data pengukuran berdasarkan ID (API untuk Android)
     */
    public function getPengukuranById()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id diperlukan!"
                ]);
            }

            $pengukuran = $this->db->table('t_pengukuran_rightpiez')
                ->select('id_pengukuran, tahun, periode, tanggal, tma, ch_hujan, temp_id')
                ->where('id_pengukuran', $pengukuran_id)
                ->get()
                ->getRowArray();

            if (!$pengukuran) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data pengukuran tidak ditemukan!"
                ]);
            }

            return $this->response->setJSON([
                "status" => "success",
                "data" => $pengukuran
            ]);

        } catch (\Exception $e) {
            log_message('error', '[getPengukuranById] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data pengukuran: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data dari tabel lainnya (metrik, ireading_atas, dll)
     */
    public function getAdditionalData()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');
            $table = $this->request->getGet('table'); // metrik, ireading_atas, perhitungan_tengah, elevasi_dasar

            if (!$pengukuran_id || !$table) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id dan table diperlukan!"
                ]);
            }

            $model = null;
            switch ($table) {
                case 'metrik':
                    $model = $this->metrikModel;
                    break;
                case 'ireading_atas':
                    $model = $this->ireadingAtasModel;
                    break;
                case 'perhitungan_tengah':
                    $model = $this->perhitunganTengahModel;
                    break;
                case 'elevasi_dasar':
                    $model = $this->elevasiDasarModel;
                    break;
                default:
                    return $this->response->setJSON([
                        "status" => "error",
                        "message" => "Table tidak valid: $table"
                    ]);
            }

            $data = $model->find($pengukuran_id);

            return $this->response->setJSON([
                "status" => "success",
                "data" => $data ?: []
            ]);

        } catch (\Exception $e) {
            log_message('error', '[getAdditionalData] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data: " . $e->getMessage()
            ]);
        }
    }
}