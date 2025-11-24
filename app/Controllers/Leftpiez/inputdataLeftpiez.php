<?php

namespace App\Controllers\Leftpiez;

use CodeIgniter\Controller;
use Config\Database;
use App\Models\LeftPiez\TPengukuranLeftpiezModel;
use App\Models\LeftPiez\TPembacaanL01Model;
use App\Models\LeftPiez\TPembacaanL02Model;
use App\Models\LeftPiez\TPembacaanL03Model;
use App\Models\LeftPiez\TPembacaanL04Model;
use App\Models\LeftPiez\TPembacaanL05Model;
use App\Models\LeftPiez\TPembacaanL06Model;
use App\Models\LeftPiez\TPembacaanL07Model;
use App\Models\LeftPiez\TPembacaanL08Model;
use App\Models\LeftPiez\TPembacaanL09Model;
use App\Models\LeftPiez\TPembacaanL10Model;
use App\Models\LeftPiez\TPembacaanSpz02Model;

class InputdataLeftpiez extends Controller
{
    protected $db;
    protected $pengukuranModel;
    protected $pembacaanModels;

    public function __construct()
    {
        // Konfigurasi database db_left_piez
        $this->db = Database::connect('db_left_piez');
        
        $this->pengukuranModel = new TPengukuranLeftpiezModel();
        
        // Inisialisasi semua model pembacaan
        $this->pembacaanModels = [
            'L01' => new TPembacaanL01Model(),
            'L02' => new TPembacaanL02Model(),
            'L03' => new TPembacaanL03Model(),
            'L04' => new TPembacaanL04Model(),
            'L05' => new TPembacaanL05Model(),
            'L06' => new TPembacaanL06Model(),
            'L07' => new TPembacaanL07Model(),
            'L08' => new TPembacaanL08Model(),
            'L09' => new TPembacaanL09Model(),
            'L10' => new TPembacaanL10Model(),
            'SPZ02' => new TPembacaanSpz02Model()
        ];

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

            if (!$tahun || !$tanggal) return $this->response->setJSON(["status"=>"error","message"=>"Tahun dan Tanggal wajib diisi!"]);
            if ($periode && !preg_match('/^TW-/i', $periode) && is_numeric($periode)) $periode = "TW-" . $periode;

            // CREATE
            if (!$pengukuran_id || !is_numeric($pengukuran_id)) {
                // Cek existing
                $checkExisting = $this->db->table("t_pengukuran_leftpiez")
                    ->where("tahun", $tahun)->where("periode", $periode)->where("tanggal", $tanggal)->get()->getRow();

                if ($checkExisting) return $this->response->setJSON([
                    "status"=>"error",
                    "message"=>"Data pengukuran dengan Tahun $tahun, Periode $periode, dan Tanggal $tanggal sudah ada!",
                    "pengukuran_id"=>$checkExisting->id_pengukuran
                ]);

                $insertData = ["tahun"=>$tahun,"periode"=>$periode,"tanggal"=>$tanggal,"dma"=>$dma,"temp_id"=>$temp_id];
                $this->pengukuranModel->save($insertData); // pakai save() supaya aman
                $pengukuran_id = $this->pengukuranModel->getInsertID();

                // INSERT METRIK DEFAULT MUTLAK - SESUAIKAN DENGAN STRUKTUR TABEL
                $this->db->table("b_piezo_metrik")->insert([
                    "id_pengukuran" => $pengukuran_id,
                    "M_feet" => 0.3048,   // gunakan M_feet sesuai struktur tabel
                    "M_inch" => 0.0254,   // gunakan M_inch sesuai struktur tabel
                    "l_01" => null,
                    "l_02" => null,
                    "l_03" => null,
                    "l_04" => null,
                    "l_05" => null,
                    "l_06" => null,
                    "l_07" => null,
                    "l_08" => null,
                    "l_09" => null,
                    "l_10" => null,
                    "spz_02" => null,
                    "created_at" => date("Y-m-d H:i:s"),
                    "updated_at" => date("Y-m-d H:i:s")
                ]);

                return $this->response->setJSON(["status"=>"success","message"=>"Data pengukuran berhasil dibuat.","pengukuran_id"=>$pengukuran_id]);
            }

            // UPDATE
            $updateData = [];
            if ($dma !== null) $updateData["dma"] = $dma;
            if ($periode !== null) $updateData["periode"] = $periode;
            if ($temp_id !== null) $updateData["temp_id"] = $temp_id;
            if (!empty($updateData)) $this->pengukuranModel->update($pengukuran_id, $updateData);

            return $this->response->setJSON(["status"=>"success","message"=>"Data pengukuran berhasil diperbarui.","pengukuran_id"=>$pengukuran_id]);

        } catch (\Exception $e) {
            return $this->response->setJSON(["status"=>"error","message"=>"Terjadi kesalahan saat menyimpan data pengukuran: ".$e->getMessage()]);
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
            if (!array_key_exists($lokasi, $this->pembacaanModels)) return $this->response->setJSON(["status"=>"error","message"=>"Lokasi tidak valid: $lokasi"]);
            $model = $this->pembacaanModels[$lokasi];

            // Ambil input user untuk feet/inch
            $feet = $this->getVal('feet', $data);
            $inch = $this->getVal('inch', $data);

            $existing = $model->where("id_pengukuran", $pengukuran_id)->first();
            if ($existing) return $this->response->setJSON(["status"=>"error","message"=>"Data pembacaan $lokasi sudah ada!"]);

            $insertData = ["id_pengukuran"=>$pengukuran_id, "feet"=>$feet, "inch"=>$inch];
            $insertData = array_filter($insertData,function($v){return $v!==null;});

            $model->save($insertData);

            return $this->response->setJSON(["status"=>"success","message"=>"Data pembacaan $lokasi berhasil disimpan."]);

        } catch (\Exception $e) {
            return $this->response->setJSON(["status"=>"error","message"=>"Terjadi kesalahan saat menyimpan data pembacaan $lokasi: ".$e->getMessage()]);
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
            if (!array_key_exists($lokasi, $this->pembacaanModels)) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Lokasi tidak valid: $lokasi"
                ]);
            }

            $model = $this->pembacaanModels[$lokasi];
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

            $allData = [];

            // Ambil data dari semua lokasi
            foreach ($this->pembacaanModels as $lokasi => $model) {
                $data = $model->where('id_pengukuran', $pengukuran_id)->first();
                if ($data) {
                    $allData[$lokasi] = $data;
                }
            }

            return $this->response->setJSON([
                "status" => "success",
                "data" => $allData
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
}