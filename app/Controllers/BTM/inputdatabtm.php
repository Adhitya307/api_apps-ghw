<?php

namespace App\Controllers\Btm;

use CodeIgniter\Controller;
use Config\Database;
use App\Models\Btm\BacaanBt1Model;
use App\Models\Btm\BacaanBt2Model;
use App\Models\Btm\BacaanBt3Model;
use App\Models\Btm\BacaanBt4Model;
use App\Models\Btm\BacaanBt5Model;
use App\Models\Btm\BacaanBt6Model;
use App\Models\Btm\BacaanBt7Model;
use App\Models\Btm\BacaanBt8Model;
use App\Models\Btm\PengukuranBtmModel;

class InputDataBtm extends Controller
{
    protected $db;
    protected $pengukuranModel;
    protected $bacaanBt1Model;
    protected $bacaanBt2Model;
    protected $bacaanBt3Model;
    protected $bacaanBt4Model;
    protected $bacaanBt5Model;
    protected $bacaanBt6Model;
    protected $bacaanBt7Model;
    protected $bacaanBt8Model;

    public function __construct()
    {
        // Konfigurasi database BTM
        $this->db = Database::connect('btm');
        
        $this->pengukuranModel = new PengukuranBtmModel();
        $this->bacaanBt1Model = new BacaanBt1Model();
        $this->bacaanBt2Model = new BacaanBt2Model();
        $this->bacaanBt3Model = new BacaanBt3Model();
        $this->bacaanBt4Model = new BacaanBt4Model();
        $this->bacaanBt5Model = new BacaanBt5Model();
        $this->bacaanBt6Model = new BacaanBt6Model();
        $this->bacaanBt7Model = new BacaanBt7Model();
        $this->bacaanBt8Model = new BacaanBt8Model();

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
            log_message('debug', '[InputDataBtm] Raw input: ' . $rawInput);
            
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

            log_message('debug', "[InputDataBtm] mode={$mode}, pengukuran_id={$pengukuran_id}, temp_id={$temp_id}");

            if (!$mode) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter mode wajib dikirim!"
                ]);
            }

            if ($mode === "pengukuran") {
                return $this->savePengukuran($data, $temp_id);
            }

            if ((!$pengukuran_id || !is_numeric($pengukuran_id)) && $temp_id) {
                $row = $this->db->table("t_pengukuran_btm")
                    ->where("temp_id", $temp_id)
                    ->get()
                    ->getRow();
                if ($row) {
                    $pengukuran_id = $row->id_pengukuran;
                    log_message('debug', "[InputDataBtm] pengukuran_id ditemukan dari temp_id={$temp_id} → id={$pengukuran_id}");
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

            // Handle mode bubbletilt
            if ($mode === "bubbletilt") {
                return $this->saveBubbleTilt($data, $pengukuran_id);
            }

            // Handle mode lainnya (bt1, bt2, etc)
            switch ($mode) {
                case "bt1":
                    return $this->saveBacaanBt1($data, $pengukuran_id);
                case "bt2":
                    return $this->saveBacaanBt2($data, $pengukuran_id);
                case "bt3":
                    return $this->saveBacaanBt3($data, $pengukuran_id);
                case "bt4":
                    return $this->saveBacaanBt4($data, $pengukuran_id);
                case "bt5":
                    return $this->saveBacaanBt5($data, $pengukuran_id);
                case "bt6":
                    return $this->saveBacaanBt6($data, $pengukuran_id);
                case "bt7":
                    return $this->saveBacaanBt7($data, $pengukuran_id);
                case "bt8":
                    return $this->saveBacaanBt8($data, $pengukuran_id);
                default:
                    return $this->response->setJSON([
                        "status" => "error",
                        "message" => "Mode tidak dikenali: $mode"
                    ]);
            }
        } catch (\Exception $e) {
            log_message('error', '[InputDataBtm] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan server: " . $e->getMessage()
            ]);
        }
    }

    private function savePengukuran($data, $temp_id)
    {
        try {
            log_message('debug', '[savePengukuran] Data received: ' . json_encode($data));
            
            $pengukuran_id = $this->getVal('pengukuran_id', $data);
            $tahun   = $this->getVal('tahun', $data);
            $bulan   = $this->getVal('bulan', $data);
            $periode = $this->getVal('periode', $data);
            $tanggal = $this->getVal('tanggal', $data);

            log_message('debug', "[savePengukuran] Parsed: tahun=$tahun, bulan=$bulan, periode=$periode, tanggal=$tanggal");

            // Jika tidak ada pengukuran_id → wajib tahun & tanggal
            if (!$tahun || !$tanggal) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Tahun dan Tanggal wajib diisi!"
                ]);
            }

            // Format periode
            if ($periode && !preg_match('/^TW-/i', $periode)) {
                if (is_numeric($periode)) {
                    $periode = "TW-" . $periode;
                }
            }

            // Cek apakah data sudah ada berdasarkan tahun & tanggal
            $check = $this->db->table("t_pengukuran_btm")
                ->where("tahun", $tahun)
                ->where("tanggal", $tanggal)
                ->get()
                ->getRow();

            log_message('debug', "[savePengukuran] Check existing: " . ($check ? 'exists' : 'not exists'));

            if ($check) {
                return $this->response->setJSON([
                    "status" => "info",
                    "message" => "Data pengukuran sudah ada.",
                    "pengukuran_id" => $check->id_pengukuran
                ]);
            }

            // Insert baru jika belum ada data
            $insertData = [
                "tahun" => $tahun,
                "periode" => $periode,
                "tanggal" => $tanggal,
                "temp_id" => $temp_id
            ];

            log_message('debug', '[savePengukuran] Insert data: ' . json_encode($insertData));

            $this->db->transStart();

            if (!$this->pengukuranModel->insert($insertData)) {
                $error = $this->pengukuranModel->errors();
                log_message('error', '[savePengukuran] Model error: ' . json_encode($error));
                $this->db->transRollback();
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Gagal menyimpan data pengukuran: " . implode(', ', $error)
                ]);
            }

            $pengukuran_id = $this->pengukuranModel->getInsertID();
            log_message('debug', "[savePengukuran] Insert success, ID: $pengukuran_id");

            $this->db->transComplete();

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Data pengukuran berhasil dibuat.",
                "pengukuran_id" => $pengukuran_id
            ]);

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', '[savePengukuran] Exception: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat menyimpan data pengukuran: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk menyimpan data Bubble Tilt (US_GP, US_Arah, TB_GP, TB_Arah)
     */
    private function saveBubbleTilt($data, $pengukuran_id)
    {
        try {
            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Silakan pilih data pengukuran terlebih dahulu!"
                ]);
            }

            $bt_number = $this->getVal('bt_number', $data);
            if (!$bt_number || !in_array($bt_number, [1, 2, 3, 4, 5, 6, 7, 8])) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "BT number harus antara 1-8!"
                ]);
            }

            // Pilih model berdasarkan BT number
            $model = $this->getBacaanModel($bt_number);

            // Mapping field dari Android ke database
            $us_gp = $this->getVal('us_gp', $data);
            $us_arah = $this->getVal('us_arah', $data);
            $tb_gp = $this->getVal('tb_gp', $data);
            $tb_arah = $this->getVal('tb_arah', $data);

            $insertData = [
                "id_pengukuran" => $pengukuran_id,
                "US_GP" => $us_gp,
                "US_Arah" => $us_arah,
                "TB_GP" => $tb_gp,
                "TB_Arah" => $tb_arah
            ];

            // Hapus null values
            $insertData = array_filter($insertData, function($value) {
                return $value !== null;
            });

            if (empty($insertData)) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Minimal satu nilai Bubble Tilt harus diisi!"
                ]);
            }

            // Cek apakah data sudah ada
            $existing = $model->where("id_pengukuran", $pengukuran_id)->first();

            $this->db->transStart();

            if ($existing) {
                // ✅ VALIDASI: Jika data sudah ada, kembalikan pesan info (jangan update)
                return $this->response->setJSON([
                    "status" => "info",
                    "message" => "Data Bubble Tilt BT$bt_number sudah ada. Tidak dapat diperbarui."
                ]);
            } else {
                // Insert data baru
                $model->insert($insertData);
                $msg = "Data Bubble Tilt BT$bt_number berhasil disimpan.";
            }

            $this->db->transComplete();

            return $this->response->setJSON([
                "status" => "success",
                "message" => $msg
            ]);

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', '[saveBubbleTilt] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat menyimpan data Bubble Tilt: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan model berdasarkan BT number
     */
    private function getBacaanModel($bt_number)
    {
        switch ($bt_number) {
            case 1: return $this->bacaanBt1Model;
            case 2: return $this->bacaanBt2Model;
            case 3: return $this->bacaanBt3Model;
            case 4: return $this->bacaanBt4Model;
            case 5: return $this->bacaanBt5Model;
            case 6: return $this->bacaanBt6Model;
            case 7: return $this->bacaanBt7Model;
            case 8: return $this->bacaanBt8Model;
            default: return $this->bacaanBt1Model;
        }
    }

    /**
     * Method untuk menyimpan data bacaan individual (BT1-BT8)
     */
    private function saveBacaanBt1($data, $pengukuran_id) { 
        return $this->saveBacaanGeneric($data, $pengukuran_id, 1, $this->bacaanBt1Model); 
    }
    
    private function saveBacaanBt2($data, $pengukuran_id) { 
        return $this->saveBacaanGeneric($data, $pengukuran_id, 2, $this->bacaanBt2Model); 
    }
    
    private function saveBacaanBt3($data, $pengukuran_id) { 
        return $this->saveBacaanGeneric($data, $pengukuran_id, 3, $this->bacaanBt3Model); 
    }
    
    private function saveBacaanBt4($data, $pengukuran_id) { 
        return $this->saveBacaanGeneric($data, $pengukuran_id, 4, $this->bacaanBt4Model); 
    }
    
    private function saveBacaanBt5($data, $pengukuran_id) { 
        return $this->saveBacaanGeneric($data, $pengukuran_id, 5, $this->bacaanBt5Model); 
    }
    
    private function saveBacaanBt6($data, $pengukuran_id) { 
        return $this->saveBacaanGeneric($data, $pengukuran_id, 6, $this->bacaanBt6Model); 
    }
    
    private function saveBacaanBt7($data, $pengukuran_id) { 
        return $this->saveBacaanGeneric($data, $pengukuran_id, 7, $this->bacaanBt7Model); 
    }
    
    private function saveBacaanBt8($data, $pengukuran_id) { 
        return $this->saveBacaanGeneric($data, $pengukuran_id, 8, $this->bacaanBt8Model); 
    }

    private function saveBacaanGeneric($data, $pengukuran_id, $bt_number, $model)
    {
        try {
            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Silakan pilih data pengukuran terlebih dahulu!"
                ]);
            }

            $bacaan = $this->getVal('bacaan', $data);
            
            if ($bacaan === null) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Nilai bacaan harus diisi!"
                ]);
            }

            $insertData = [
                "id_pengukuran" => $pengukuran_id,
                "bacaan" => $bacaan
            ];

            // Cek apakah data sudah ada
            $existing = $model->where("id_pengukuran", $pengukuran_id)->first();

            $this->db->transStart();

            if ($existing) {
                // ✅ VALIDASI: Jika data sudah ada, kembalikan pesan info (jangan update)
                return $this->response->setJSON([
                    "status" => "info", 
                    "message" => "Data bacaan BT$bt_number sudah ada. Tidak dapat diperbarui."
                ]);
            } else {
                // Insert data baru
                $model->insert($insertData);
                $msg = "Data bacaan BT$bt_number berhasil disimpan.";
            }

            $this->db->transComplete();

            return $this->response->setJSON([
                "status" => "success",
                "message" => $msg
            ]);

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', "[saveBacaanGeneric BT$bt_number] Error: " . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat menyimpan data bacaan BT$bt_number: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data pengukuran (API untuk Android)
     */
    public function getPengukuran()
    {
        try {
            $pengukuran = $this->db->table('t_pengukuran_btm')
                ->select('id_pengukuran, tahun, periode, tanggal, temp_id')
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
     * Method untuk mendapatkan data berdasarkan pengukuran_id dan BT number
     */
    public function getData()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');
            $bt = $this->request->getGet('bt');

            if (!$pengukuran_id || !$bt) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id dan bt diperlukan!"
                ]);
            }

            $model = $this->getBacaanModel($bt);
            $data = $model->where('id_pengukuran', $pengukuran_id)->first();

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
     * Method untuk kalkulasi/perhitungan Bubble Tilt
     */
    public function hitungBubbleTilt()
    {
        try {
            $pengukuran_id = $this->request->getPost('pengukuran_id');

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id diperlukan!"
                ]);
            }

            // Implementasi logika perhitungan Bubble Tilt di sini
            // Contoh sederhana:
            $hasilPerhitungan = [
                'deflection_x' => 0.0,
                'deflection_y' => 0.0,
                'total_deflection' => 0.0,
                'status' => 'normal'
            ];

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Perhitungan Bubble Tilt berhasil",
                "data" => $hasilPerhitungan
            ]);

        } catch (\Exception $e) {
            log_message('error', '[hitungBubbleTilt] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal melakukan perhitungan: " . $e->getMessage()
            ]);
        }
    }
}