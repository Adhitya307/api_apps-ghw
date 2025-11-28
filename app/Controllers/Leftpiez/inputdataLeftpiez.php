<?php

namespace App\Controllers\Leftpiez;

use CodeIgniter\Controller;
use Config\Database;
use App\Models\LeftPiez\TPengukuranLeftpiezModel;
use App\Models\LeftPiez\TPembacaanLeftPiezModel; // Model pembacaan terpadu
use App\Models\LeftPiez\IreadingA; // Model untuk initial readings A
use App\Models\LeftPiez\IreadingB; // Model untuk initial readings B

class InputdataLeftpiez extends Controller
{
    protected $db;
    protected $pengukuranModel;
    protected $pembacaanModel; // Model pembacaan terpadu

    public function __construct()
    {
        // Konfigurasi database db_left_piez
        $this->db = Database::connect('db_left_piez');
        
        $this->pengukuranModel = new TPengukuranLeftpiezModel();
        $this->pembacaanModel = new TPembacaanLeftPiezModel(); // Model terpadu

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
            log_message('debug', '[InputdataLeftpiez] Raw input: ' . $rawInput);
            
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

            log_message('debug', "[InputdataLeftpiez] mode={$mode}, pengukuran_id={$pengukuran_id}, temp_id={$temp_id}");

            if (!$mode) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter mode wajib dikirim!"
                ]);
            }

            // Mode update_dma sebelum mode pengukuran
            if ($mode === "update_dma") {
                return $this->updateDMAPengukuran($data, $pengukuran_id);
            }

            if ($mode === "pengukuran") {
                return $this->savePengukuran($data, $temp_id);
            }

            if ((!$pengukuran_id || !is_numeric($pengukuran_id)) && $temp_id) {
                $row = $this->db->table("t_pengukuran_leftpiez")
                    ->where("temp_id", $temp_id)
                    ->get()
                    ->getRow();
                if ($row) {
                    $pengukuran_id = $row->id_pengukuran;
                    log_message('debug', "[InputdataLeftpiez] pengukuran_id ditemukan dari temp_id={$temp_id} → id={$pengukuran_id}");
                } else {
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

            // Handle mode pembacaan untuk semua L01-L10 dan SPZ02
            if (strpos($mode, "pembacaan_") === 0) {
                $lokasi = strtoupper(str_replace("pembacaan_", "", $mode));
                return $this->savePembacaan($data, $pengukuran_id, $lokasi);
            }

            return $this->response->setJSON([
                "status" => "error",
                "message" => "Mode tidak dikenali: $mode"
            ]);

        } catch (\Exception $e) {
            log_message('error', '[InputdataLeftpiez] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan server: " . $e->getMessage()
            ]);
        }
    }

    private function savePengukuran($data, $temp_id)
    {
        try {
            $pengukuran_id = $this->getVal('pengukuran_id', $data);
            $tahun   = $this->getVal('tahun', $data);
            $periode = $this->getVal('periode', $data);
            $tanggal = $this->getVal('tanggal', $data);
            $dma     = $this->getVal('dma', $data);

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
                // Cek existing
                $checkExisting = $this->db->table("t_pengukuran_leftpiez")
                    ->where("tahun", $tahun)
                    ->where("periode", $periode)
                    ->where("tanggal", $tanggal)
                    ->get()
                    ->getRow();

                if ($checkExisting) {
                    return $this->response->setJSON([
                        "status" => "error",
                        "message" => "Data pengukuran dengan Tahun $tahun, Periode $periode, dan Tanggal $tanggal sudah ada!",
                        "pengukuran_id" => $checkExisting->id_pengukuran
                    ]);
                }

                $insertData = [
                    "tahun" => $tahun,
                    "periode" => $periode,
                    "tanggal" => $tanggal,
                    "dma" => $dma,
                    "temp_id" => $temp_id
                ];
                
                $this->pengukuranModel->save($insertData);
                $pengukuran_id = $this->pengukuranModel->getInsertID();

                // ✅ INSERT INITIAL READINGS A OTOMATIS SETELAH CREATE PENGUKURAN
                $initialValuesA = [
                    'L_01' => 650.64,
                    'L_02' => 650.60,
                    'L_03' => 616.55,
                    'L_04' => 580.26,
                    'L_05' => 700.76,
                    'L_06' => 690.09,
                    'L_07' => 653.36,
                    'L_08' => 659.14,
                    'L_09' => 622.45,
                    'L_10' => 580.36,
                    'SPZ_02' => 700.08
                ];

                $ireadingA = new IreadingA();
                $ireadingA->insertInitialReadings($pengukuran_id, $initialValuesA);

                // ✅ INSERT INITIAL READINGS B OTOMATIS SETELAH CREATE PENGUKURAN
                $initialValuesB = [
                    'L_01'  => 71.5,
                    'L_02'  => 73,
                    'L_03'  => 59,
                    'L_04'  => 50,
                    'L_05'  => 62,
                    'L_06'  => 62,
                    'L_07'  => 40,
                    'L_08'  => 55.5,
                    'L_09'  => 57,
                    'L_10'  => 51.5,
                    'SPZ_02'=> 70
                ];

                $ireadingB = new IreadingB();
                $ireadingB->insertInitialReadings($pengukuran_id, $initialValuesB);

                log_message('debug', '[InitialReadings] Insert otomatis A & B untuk pengukuran_id=' . $pengukuran_id);

                return $this->response->setJSON([
                    "status" => "success",
                    "message" => "Data pengukuran & initial readings A & B berhasil dibuat.",
                    "pengukuran_id" => $pengukuran_id
                ]);
            }

            // UPDATE
            $updateData = [];
            if ($dma !== null) $updateData["dma"] = $dma;
            if ($periode !== null) $updateData["periode"] = $periode;
            if ($temp_id !== null) $updateData["temp_id"] = $temp_id;
            
            if (!empty($updateData)) {
                $this->pengukuranModel->update($pengukuran_id, $updateData);
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Data pengukuran berhasil diperbarui.",
                "pengukuran_id" => $pengukuran_id
            ]);

        } catch (\Exception $e) {
            log_message('error', '[savePengukuran] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat menyimpan data pengukuran: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk update DMA saja
     */
    private function updateDMAPengukuran($data, $pengukuran_id)
    {
        try {
            $dma = $this->getVal('dma', $data);
            
            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "pengukuran_id diperlukan untuk update DMA!"
                ]);
            }

            // Cek apakah data pengukuran ada
            $existing = $this->db->table("t_pengukuran_leftpiez")
                ->where("id_pengukuran", $pengukuran_id)
                ->get()
                ->getRow();

            if (!$existing) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data pengukuran dengan ID $pengukuran_id tidak ditemukan!"
                ]);
            }

            // Update DMA
            $this->db->table("t_pengukuran_leftpiez")
                ->where("id_pengukuran", $pengukuran_id)
                ->update(["dma" => $dma]);

            log_message('debug', "[updateDMAPengukuran] DMA updated for ID: $pengukuran_id, DMA: $dma");

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Data DMA berhasil diperbarui.",
                "pengukuran_id" => $pengukuran_id
            ]);

        } catch (\Exception $e) {
            log_message('error', '[updateDMAPengukuran] Exception: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat update DMA: " . $e->getMessage()
            ]);
        }
    }

    private function savePembacaan($data, $pengukuran_id, $lokasi)
    {
        try {
            // Validasi lokasi
            $lokasi = strtoupper($lokasi);
            if (!$this->pembacaanModel->isValidPiezometer($lokasi)) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Lokasi tidak valid: $lokasi"
                ]);
            }

            // Ambil input user untuk feet/inch
            $feet = $this->getVal('feet', $data);
            $inch = $this->getVal('inch', $data);

            // Cek apakah data pembacaan sudah ada
            $existing = $this->pembacaanModel->getByPengukuranDanTipe($pengukuran_id, $lokasi);
            
            if ($existing) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data pembacaan $lokasi sudah ada!"
                ]);
            }

            // Simpan data pembacaan menggunakan model terpadu
            $insertData = [
                "id_pengukuran" => $pengukuran_id,
                "tipe_piezometer" => $lokasi,
                "feet" => $feet,
                "inch" => $inch
            ];

            // Filter null values
            $insertData = array_filter($insertData, function($v) { 
                return $v !== null; 
            });

            $this->pembacaanModel->insert($insertData);

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Data pembacaan $lokasi berhasil disimpan."
            ]);

        } catch (\Exception $e) {
            log_message('error', "[savePembacaan] Error for $lokasi: " . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat menyimpan data pembacaan $lokasi: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data pengukuran (API untuk Android)
     */
    public function getPengukuran()
    {
        try {
            $pengukuran = $this->db->table('t_pengukuran_leftpiez')
                ->select('id_pengukuran, tahun, periode, tanggal, dma, temp_id')
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
     * Method untuk mendapatkan data pembacaan berdasarkan pengukuran_id dan lokasi
     */
    public function getData()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');
            $lokasi = $this->request->getGet('lokasi'); // L01, L02, ..., L10, SPZ02

            if (!$pengukuran_id || !$lokasi) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id dan lokasi diperlukan!"
                ]);
            }

            // Validasi lokasi
            $lokasi = strtoupper($lokasi);
            if (!$this->pembacaanModel->isValidPiezometer($lokasi)) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Lokasi tidak valid: $lokasi"
                ]);
            }

            $data = $this->pembacaanModel->getByPengukuranDanTipe($pengukuran_id, $lokasi);

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

            // Ambil semua data pembacaan sekaligus
            $allData = $this->pembacaanModel->getByPengukuran($pengukuran_id);

            // Format data untuk response
            $formattedData = [];
            foreach ($allData as $data) {
                $formattedData[$data['tipe_piezometer']] = $data;
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

            $pengukuran = $this->db->table('t_pengukuran_leftpiez')
                ->select('id_pengukuran, tahun, periode, tanggal, dma, temp_id')
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
     * Method untuk update data pembacaan yang sudah ada
     */
    public function updatePembacaan()
    {
        try {
            $data = $this->request->getJSON(true);
            
            $pengukuran_id = $this->getVal('pengukuran_id', $data);
            $lokasi = $this->getVal('lokasi', $data);
            $feet = $this->getVal('feet', $data);
            $inch = $this->getVal('inch', $data);

            if (!$pengukuran_id || !$lokasi) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id dan lokasi diperlukan!"
                ]);
            }

            // Validasi lokasi
            $lokasi = strtoupper($lokasi);
            if (!$this->pembacaanModel->isValidPiezometer($lokasi)) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Lokasi tidak valid: $lokasi"
                ]);
            }

            // Cek apakah data pembacaan sudah ada
            $existing = $this->pembacaanModel->getByPengukuranDanTipe($pengukuran_id, $lokasi);
            
            if (!$existing) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data pembacaan $lokasi tidak ditemukan!"
                ]);
            }

            // Update data pembacaan
            $updateData = [];
            if ($feet !== null) $updateData['feet'] = $feet;
            if ($inch !== null) $updateData['inch'] = $inch;

            if (!empty($updateData)) {
                $this->pembacaanModel->update($existing['id_pembacaan'], $updateData);
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Data pembacaan $lokasi berhasil diperbarui."
            ]);

        } catch (\Exception $e) {
            log_message('error', '[updatePembacaan] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal memperbarui data pembacaan: " . $e->getMessage()
            ]);
        }
    }
}