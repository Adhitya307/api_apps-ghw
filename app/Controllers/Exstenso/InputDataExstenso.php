<?php

namespace App\Controllers\Exstenso;

use CodeIgniter\Controller;
use Config\Database;
use App\Models\Exstenso\PengukuranEksModel;
use App\Models\Exstenso\PembacaanEx1Model;
use App\Models\Exstenso\PembacaanEx2Model;
use App\Models\Exstenso\PembacaanEx3Model;
use App\Models\Exstenso\PembacaanEx4Model;
use App\Models\Exstenso\ReadingsEx1Model;
use App\Models\Exstenso\ReadingsEx2Model;
use App\Models\Exstenso\ReadingsEx3Model;
use App\Models\Exstenso\ReadingsEx4Model;

class InputDataExstenso extends Controller
{
    protected $db;
    protected $pengukuranModel;
    protected $pembacaanEx1Model;
    protected $pembacaanEx2Model;
    protected $pembacaanEx3Model;
    protected $pembacaanEx4Model;
    protected $readingsEx1Model;
    protected $readingsEx2Model;
    protected $readingsEx3Model;
    protected $readingsEx4Model;

    public function __construct()
    {
        // Konfigurasi database exs
        $this->db = Database::connect('db_exs');
        
        $this->pengukuranModel = new PengukuranEksModel();
        $this->pembacaanEx1Model = new PembacaanEx1Model();
        $this->pembacaanEx2Model = new PembacaanEx2Model();
        $this->pembacaanEx3Model = new PembacaanEx3Model();
        $this->pembacaanEx4Model = new PembacaanEx4Model();
        $this->readingsEx1Model = new ReadingsEx1Model();
        $this->readingsEx2Model = new ReadingsEx2Model();
        $this->readingsEx3Model = new ReadingsEx3Model();
        $this->readingsEx4Model = new ReadingsEx4Model();

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
            log_message('debug', '[InputDataExstenso] Raw input: ' . $rawInput);
            
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

            log_message('debug', "[InputDataExstenso] mode={$mode}, pengukuran_id={$pengukuran_id}, temp_id={$temp_id}");

            if (!$mode) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter mode wajib dikirim!"
                ]);
            }

            // PERBAIKAN: Tambahkan mode update_dma sebelum mode pengukuran
            if ($mode === "update_dma") {
                return $this->updateDMAPengukuran($data, $pengukuran_id);
            }

            if ($mode === "pengukuran") {
                return $this->savePengukuran($data, $temp_id);
            }

            if ((!$pengukuran_id || !is_numeric($pengukuran_id)) && $temp_id) {
                $row = $this->db->table("t_pengukuran_eks")
                    ->where("temp_id", $temp_id)
                    ->get()
                    ->getRow();
                if ($row) {
                    $pengukuran_id = $row->id_pengukuran;
                    log_message('debug', "[InputDataExstenso] pengukuran_id ditemukan dari temp_id={$temp_id} → id={$pengukuran_id}");
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

            // Handle mode pembacaan
            switch ($mode) {
                case "pembacaan_ex1":
                    return $this->savePembacaanEx1($data, $pengukuran_id);
                case "pembacaan_ex2":
                    return $this->savePembacaanEx2($data, $pengukuran_id);
                case "pembacaan_ex3":
                    return $this->savePembacaanEx3($data, $pengukuran_id);
                case "pembacaan_ex4":
                    return $this->savePembacaanEx4($data, $pengukuran_id);
                case "readings_ex1":
                    return $this->saveReadingsEx1($data, $pengukuran_id);
                case "readings_ex2":
                    return $this->saveReadingsEx2($data, $pengukuran_id);
                case "readings_ex3":
                    return $this->saveReadingsEx3($data, $pengukuran_id);
                case "readings_ex4":
                    return $this->saveReadingsEx4($data, $pengukuran_id);
                default:
                    return $this->response->setJSON([
                        "status" => "error",
                        "message" => "Mode tidak dikenali: $mode"
                    ]);
            }
        } catch (\Exception $e) {
            log_message('error', '[InputDataExstenso] Error: ' . $e->getMessage());
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
            $periode = $this->getVal('periode', $data);
            $tanggal = $this->getVal('tanggal', $data);
            $dma     = $this->getVal('dma', $data);

            log_message('debug', "[savePengukuran] Parsed: tahun=$tahun, periode=$periode, tanggal=$tanggal, dma=$dma");

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

            // PERBAIKAN: Cek apakah ini UPDATE atau CREATE
            if ($pengukuran_id && is_numeric($pengukuran_id)) {
                // MODE UPDATE: Cek apakah pengukuran_id ada di database
                $check = $this->db->table("t_pengukuran_eks")
                    ->where("id_pengukuran", $pengukuran_id)
                    ->get()
                    ->getRow();

                if ($check) {
                    // UPDATE data yang sudah ada
                    $updateData = [];
                    if ($dma !== null) $updateData["dma"] = $dma;
                    if ($periode !== null) $updateData["periode"] = $periode;
                    
                    // Hanya update jika ada field yang berubah
                    if (!empty($updateData)) {
                        $this->db->table("t_pengukuran_eks")
                            ->where("id_pengukuran", $pengukuran_id)
                            ->update($updateData);
                        
                        log_message('debug', "[savePengukuran] Updated pengukuran ID: $pengukuran_id");
                    }

                    return $this->response->setJSON([
                        "status" => "success",
                        "message" => "Data pengukuran berhasil diperbarui.",
                        "pengukuran_id" => $pengukuran_id
                    ]);
                } else {
                    return $this->response->setJSON([
                        "status" => "error",
                        "message" => "Data pengukuran dengan ID $pengukuran_id tidak ditemukan!"
                    ]);
                }
            } else {
                // MODE CREATE: Cek duplikasi dengan logika yang lebih longgar
                $check = $this->db->table("t_pengukuran_eks")
                    ->where("tahun", $tahun)
                    ->where("tanggal", $tanggal)
                    ->where("periode", $periode)
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
                    "dma" => $dma,
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
            }

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
     * PERBAIKAN: Method baru untuk update DMA saja
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
            $existing = $this->db->table("t_pengukuran_eks")
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
            $this->db->table("t_pengukuran_eks")
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

    /**
     * Method untuk menyimpan data Pembacaan Ex1-Ex4
     */
    private function savePembacaanEx1($data, $pengukuran_id) { 
        return $this->savePembacaanGeneric($data, $pengukuran_id, 1, $this->pembacaanEx1Model); 
    }
    
    private function savePembacaanEx2($data, $pengukuran_id) { 
        return $this->savePembacaanGeneric($data, $pengukuran_id, 2, $this->pembacaanEx2Model); 
    }
    
    private function savePembacaanEx3($data, $pengukuran_id) { 
        return $this->savePembacaanGeneric($data, $pengukuran_id, 3, $this->pembacaanEx3Model); 
    }
    
    private function savePembacaanEx4($data, $pengukuran_id) { 
        return $this->savePembacaanGeneric($data, $pengukuran_id, 4, $this->pembacaanEx4Model); 
    }

    private function savePembacaanGeneric($data, $pengukuran_id, $ex_number, $model)
    {
        try {
            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Silakan pilih data pengukuran terlebih dahulu!"
                ]);
            }

            // Ambil nilai pembacaan 10, 20, 30
            $pembacaan_10 = $this->getVal('pembacaan_10', $data);
            $pembacaan_20 = $this->getVal('pembacaan_20', $data);
            $pembacaan_30 = $this->getVal('pembacaan_30', $data);

            // Validasi minimal satu nilai harus diisi
            if ($pembacaan_10 === null && $pembacaan_20 === null && $pembacaan_30 === null) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Minimal satu nilai pembacaan harus diisi!"
                ]);
            }

            $insertData = [
                "id_pengukuran" => $pengukuran_id,
                "pembacaan_10" => $pembacaan_10,
                "pembacaan_20" => $pembacaan_20,
                "pembacaan_30" => $pembacaan_30
            ];

            // Hapus null values
            $insertData = array_filter($insertData, function($value) {
                return $value !== null;
            });

            // Cek apakah data sudah ada
            $existing = $model->where("id_pengukuran", $pengukuran_id)->first();

            $this->db->transStart();

            if ($existing) {
                // Jika data sudah ada, update
                $model->update($existing[$model->primaryKey], $insertData);
                $msg = "Data pembacaan Ex$ex_number berhasil diperbarui.";
            } else {
                // Insert data baru
                $model->insert($insertData);
                $msg = "Data pembacaan Ex$ex_number berhasil disimpan.";
            }

            $this->db->transComplete();

            return $this->response->setJSON([
                "status" => "success",
                "message" => $msg
            ]);

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', "[savePembacaanGeneric Ex$ex_number] Error: " . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat menyimpan data pembacaan Ex$ex_number: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk menyimpan data Readings Ex1-Ex4 (nilai mutlak)
     */
    private function saveReadingsEx1($data, $pengukuran_id) { 
        return $this->saveReadingsGeneric($data, $pengukuran_id, 1, $this->readingsEx1Model); 
    }
    
    private function saveReadingsEx2($data, $pengukuran_id) { 
        return $this->saveReadingsGeneric($data, $pengukuran_id, 2, $this->readingsEx2Model); 
    }
    
    private function saveReadingsEx3($data, $pengukuran_id) { 
        return $this->saveReadingsGeneric($data, $pengukuran_id, 3, $this->readingsEx3Model); 
    }
    
    private function saveReadingsEx4($data, $pengukuran_id) { 
        return $this->saveReadingsGeneric($data, $pengukuran_id, 4, $this->readingsEx4Model); 
    }

    private function saveReadingsGeneric($data, $pengukuran_id, $ex_number, $model)
    {
        try {
            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Silakan pilih data pengukuran terlebih dahulu!"
                ]);
            }

            // Ambil nilai readings 10, 20, 30 (nilai mutlak)
            $reading_10 = $this->getVal('reading_10', $data);
            $reading_20 = $this->getVal('reading_20', $data);
            $reading_30 = $this->getVal('reading_30', $data);

            // Validasi minimal satu nilai harus diisi
            if ($reading_10 === null && $reading_20 === null && $reading_30 === null) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Minimal satu nilai reading harus diisi!"
                ]);
            }

            $insertData = [
                "id_pengukuran" => $pengukuran_id,
                "reading_10" => $reading_10,
                "reading_20" => $reading_20,
                "reading_30" => $reading_30
            ];

            // Hapus null values
            $insertData = array_filter($insertData, function($value) {
                return $value !== null;
            });

            // Cek apakah data sudah ada
            $existing = $model->where("id_pengukuran", $pengukuran_id)->first();

            $this->db->transStart();

            if ($existing) {
                // Jika data sudah ada, update
                $model->update($existing[$model->primaryKey], $insertData);
                $msg = "Data readings Ex$ex_number berhasil diperbarui.";
            } else {
                // Insert data baru
                $model->insert($insertData);
                $msg = "Data readings Ex$ex_number berhasil disimpan.";
            }

            $this->db->transComplete();

            return $this->response->setJSON([
                "status" => "success",
                "message" => $msg
            ]);

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', "[saveReadingsGeneric Ex$ex_number] Error: " . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat menyimpan data readings Ex$ex_number: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data pengukuran (API untuk Android)
     */
    public function getPengukuran()
    {
        try {
            $pengukuran = $this->db->table('t_pengukuran_eks')
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
     * Method untuk mendapatkan data berdasarkan pengukuran_id dan tipe data
     */
    public function getData()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');
            $type = $this->request->getGet('type'); // pembacaan_ex1, readings_ex1, dll

            if (!$pengukuran_id || !$type) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id dan type diperlukan!"
                ]);
            }

            $model = $this->getModelByType($type);
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
     * Method untuk mendapatkan model berdasarkan tipe data
     */
    private function getModelByType($type)
    {
        switch ($type) {
            case "pembacaan_ex1": return $this->pembacaanEx1Model;
            case "pembacaan_ex2": return $this->pembacaanEx2Model;
            case "pembacaan_ex3": return $this->pembacaanEx3Model;
            case "pembacaan_ex4": return $this->pembacaanEx4Model;
            case "readings_ex1": return $this->readingsEx1Model;
            case "readings_ex2": return $this->readingsEx2Model;
            case "readings_ex3": return $this->readingsEx3Model;
            case "readings_ex4": return $this->readingsEx4Model;
            default: return $this->pembacaanEx1Model;
        }
    }
}