<?php
namespace App\Controllers\DomBody;

use CodeIgniter\Controller;
use Config\Database;
use App\Models\DomBody\MPengukuranHdm;
use App\Models\DomBody\MPembacaanElv625;
use App\Models\DomBody\MPembacaanElv600;
use App\Models\DomBody\DepthElv600Model;
use App\Models\DomBody\DepthElv625Model;
use App\Models\DomBody\InitialReadingElv600Model;
use App\Models\DomBody\InitialReadingElv625Model;
use CodeIgniter\Events\Events;

class Inputdombody extends Controller
{
    protected $db;
    protected $pengukuranModel;
    protected $pembacaan625Model;
    protected $pembacaan600Model;
    protected $depth600Model;
    protected $depth625Model;
    protected $initialReading600Model;
    protected $initialReading625Model;

    public function __construct()
    {
        $this->db = Database::connect('hdm');
        $this->pengukuranModel = new MPengukuranHdm();
        $this->pembacaan625Model = new MPembacaanElv625();
        $this->pembacaan600Model = new MPembacaanElv600();
        $this->depth600Model = new DepthElv600Model(); 
        $this->depth625Model = new DepthElv625Model();
        $this->initialReading600Model = new InitialReadingElv600Model();
        $this->initialReading625Model = new InitialReadingElv625Model();

        // Support CORS untuk testing Android / Postman
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, OPTIONS");
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
            $rawInput = $this->request->getBody();
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

            log_message('debug', "[Inputdombody] mode={$mode}, pengukuran_id={$pengukuran_id}, temp_id={$temp_id}");

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
                $row = $this->db->table("t_pengukuran_hdm")
                    ->where("temp_id", $temp_id)
                    ->get()
                    ->getRow();
                if ($row) {
                    $pengukuran_id = $row->id_pengukuran;
                    log_message('debug', "[Inputdombody] pengukuran_id ditemukan dari temp_id={$temp_id} → id={$pengukuran_id}");
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
                    "message" => "Data pengukuran dengan ID $pengukuran_id tidak ditemukan di tabel t_pengukuran_hdm!"
                ]);
            }

            switch ($mode) {
                case "elv625":
                    return $this->saveElv625($data, $pengukuran_id);
                case "elv600":
                    return $this->saveElv600($data, $pengukuran_id);
                default:
                    return $this->response->setJSON([
                        "status" => "error",
                        "message" => "Mode tidak dikenali: $mode"
                    ]);
            }
        } catch (\Exception $e) {
            log_message('error', '[Inputdombody] Error: ' . $e->getMessage());
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
            $dma     = $this->getVal('dma', $data);

            log_message('debug', "[savePengukuran] Parsed: tahun=$tahun, bulan=$bulan, periode=$periode, tanggal=$tanggal");

            // Jika ada pengukuran_id → update jika DMA NULL
            if ($pengukuran_id) {
                $check = $this->pengukuranModel->find($pengukuran_id);

                if (!$check) {
                    return $this->response->setJSON([
                        "status" => "error",
                        "message" => "Data pengukuran tidak ditemukan!"
                    ]);
                }

                if ($dma !== null) {
                    if ($check['dma'] === null) {
                        $this->pengukuranModel->update($pengukuran_id, ['dma' => $dma]);

                        return $this->response->setJSON([
                            "status" => "success",
                            "message" => "DMA berhasil diperbarui.",
                            "pengukuran_id" => $pengukuran_id
                        ]);
                    } else {
                        return $this->response->setJSON([
                            "status" => "info",
                            "message" => "DMA sudah ada, tidak diperbarui.",
                            "pengukuran_id" => $pengukuran_id
                        ]);
                    }
                }

                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Tidak ada nilai DMA yang dikirim!"
                ]);
            }

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
            $check = $this->db->table("t_pengukuran_hdm")
                ->where("tahun", $tahun)
                ->where("tanggal", $tanggal)
                ->get()
                ->getRow();

            log_message('debug', "[savePengukuran] Check existing: " . ($check ? 'exists' : 'not exists'));

            if ($check) {
                if ($dma !== null) {
                    if ($check->dma === null) {
                        $this->db->table("t_pengukuran_hdm")
                            ->where("id_pengukuran", $check->id_pengukuran)
                            ->update(["dma" => $dma]);

                        return $this->response->setJSON([
                            "status" => "success",
                            "message" => "DMA berhasil diperbarui.",
                            "pengukuran_id" => $check->id_pengukuran
                        ]);
                    } else {
                        return $this->response->setJSON([
                            "status" => "info",
                            "message" => "DMA sudah ada, tidak diperbarui.",
                            "pengukuran_id" => $check->id_pengukuran
                        ]);
                    }
                }

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

            // ✅ AUTO INSERT DEPTH DEFAULT dengan PROTEKSI ANTI-DUPLIKASI
            $this->autoInsertDepth($pengukuran_id);
            
            // ✅ AUTO INSERT INITIAL READING DEFAULT dengan PROTEKSI ANTI-DUPLIKASI (FIXED)
            $this->autoInsertInitialReading($pengukuran_id);

            Events::trigger('dataPengukuranHdm:insert', $pengukuran_id);

            $this->db->transComplete();

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Data pengukuran, depth default, dan initial reading default berhasil dibuat.",
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
     * ✅ AUTO INSERT DEPTH DEFAULT dengan PROTEKSI ANTI-DUPLIKASI
     */
    private function autoInsertDepth($pengukuran_id)
    {
        try {
            log_message('debug', "[autoInsertDepth] Checking depth for pengukuran_id: $pengukuran_id");

            // ✅ PROTEKSI: Cek apakah depth ELV600 sudah ada
            $existingDepth600 = $this->depth600Model->where('id_pengukuran', $pengukuran_id)->first();
            if (!$existingDepth600) {
                $depth600Data = [
                    'id_pengukuran' => $pengukuran_id,
                    'hv_1' => 10,
                    'hv_2' => 30,
                    'hv_3' => 50,
                    'hv_4' => 70,
                    'hv_5' => 84.5
                ];
                
                if ($this->depth600Model->insert($depth600Data)) {
                    log_message('debug', "[autoInsertDepth] Depth ELV600 created for pengukuran_id: $pengukuran_id");
                } else {
                    log_message('error', "[autoInsertDepth] Failed to create Depth ELV600: " . json_encode($this->depth600Model->errors()));
                }
            } else {
                log_message('debug', "[autoInsertDepth] Depth ELV600 already exists for pengukuran_id: $pengukuran_id");
            }

            // ✅ PROTEKSI: Cek apakah depth ELV625 sudah ada
            $existingDepth625 = $this->depth625Model->where('id_pengukuran', $pengukuran_id)->first();
            if (!$existingDepth625) {
                $depth625Data = [
                    'id_pengukuran' => $pengukuran_id,
                    'hv_1' => 20,
                    'hv_2' => 40,
                    'hv_3' => 50
                ];
                
                if ($this->depth625Model->insert($depth625Data)) {
                    log_message('debug', "[autoInsertDepth] Depth ELV625 created for pengukuran_id: $pengukuran_id");
                } else {
                    log_message('error', "[autoInsertDepth] Failed to create Depth ELV625: " . json_encode($this->depth625Model->errors()));
                }
            } else {
                log_message('debug', "[autoInsertDepth] Depth ELV625 already exists for pengukuran_id: $pengukuran_id");
            }

            return true;

        } catch (\Exception $e) {
            log_message('error', "[autoInsertDepth] Error: " . $e->getMessage());
            // Jangan rollback transaksi utama, hanya log error
            return false;
        }
    }

    /**
     * ✅ AUTO INSERT INITIAL READING DEFAULT dengan PROTEKSI ANTI-DUPLIKASI (FIXED - Menggunakan nilai dari model)
     */
    private function autoInsertInitialReading($pengukuran_id)
    {
        try {
            log_message('debug', "[autoInsertInitialReading] Checking initial reading for pengukuran_id: $pengukuran_id");

            // ✅ PROTEKSI: Cek apakah initial reading ELV600 sudah ada
            $existingInitial600 = $this->initialReading600Model->where('id_pengukuran', $pengukuran_id)->first();
            if (!$existingInitial600) {
                // ✅ PERBAIKAN: Gunakan insertDefault() dari model yang sudah ada
                if ($this->initialReading600Model->insertDefault($pengukuran_id)) {
                    log_message('debug', "[autoInsertInitialReading] Initial Reading ELV600 created for pengukuran_id: $pengukuran_id");
                } else {
                    log_message('error', "[autoInsertInitialReading] Failed to create Initial Reading ELV600: " . json_encode($this->initialReading600Model->errors()));
                    
                    // ✅ FALLBACK: Jika insertDefault gagal, gunakan insert manual dengan nilai default
                    $initial600Data = [
                        'id_pengukuran' => $pengukuran_id,
                        'hv_1' => 26.60,
                        'hv_2' => 25.50,
                        'hv_3' => 24.50,
                        'hv_4' => 23.40,
                        'hv_5' => 23.60
                    ];
                    
                    if ($this->initialReading600Model->insert($initial600Data)) {
                        log_message('debug', "[autoInsertInitialReading] Initial Reading ELV600 created via fallback for pengukuran_id: $pengukuran_id");
                    } else {
                        log_message('error', "[autoInsertInitialReading] Fallback also failed for ELV600: " . json_encode($this->initialReading600Model->errors()));
                    }
                }
            } else {
                log_message('debug', "[autoInsertInitialReading] Initial Reading ELV600 already exists for pengukuran_id: $pengukuran_id");
            }

            // ✅ PROTEKSI: Cek apakah initial reading ELV625 sudah ada
            $existingInitial625 = $this->initialReading625Model->where('id_pengukuran', $pengukuran_id)->first();
            if (!$existingInitial625) {
                // ✅ PERBAIKAN: Gunakan insertDefault() dari model yang sudah ada
                if ($this->initialReading625Model->insertDefault($pengukuran_id)) {
                    log_message('debug', "[autoInsertInitialReading] Initial Reading ELV625 created for pengukuran_id: $pengukuran_id");
                } else {
                    log_message('error', "[autoInsertInitialReading] Failed to create Initial Reading ELV625: " . json_encode($this->initialReading625Model->errors()));
                    
                    // ✅ FALLBACK: Jika insertDefault gagal, gunakan insert manual dengan nilai default
                    $initial625Data = [
                        'id_pengukuran' => $pengukuran_id,
                        'hv_1' => 36.00,
                        'hv_2' => 35.50,
                        'hv_3' => 35.00
                    ];
                    
                    if ($this->initialReading625Model->insert($initial625Data)) {
                        log_message('debug', "[autoInsertInitialReading] Initial Reading ELV625 created via fallback for pengukuran_id: $pengukuran_id");
                    } else {
                        log_message('error', "[autoInsertInitialReading] Fallback also failed for ELV625: " . json_encode($this->initialReading625Model->errors()));
                    }
                }
            } else {
                log_message('debug', "[autoInsertInitialReading] Initial Reading ELV625 already exists for pengukuran_id: $pengukuran_id");
            }

            return true;

        } catch (\Exception $e) {
            log_message('error', "[autoInsertInitialReading] Error: " . $e->getMessage());
            // Jangan rollback transaksi utama, hanya log error
            return false;
        }
    }

private function saveElv625($data, $pengukuran_id)
{
    try {
        if (!$pengukuran_id) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Silakan pilih data pengukuran terlebih dahulu!"
            ]);
        }

        $fields = ['hv_1', 'hv_2', 'hv_3'];
        $insertData = ["id_pengukuran" => $pengukuran_id];
        $hasValue = false;

        foreach ($fields as $field) {
            $val = $this->getVal($field, $data);
            if ($val !== null) {
                $insertData[$field] = $val;
                $hasValue = true;
            }
        }

        if (!$hasValue) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Minimal satu nilai ELV 625 harus diisi!"
            ]);
        }

        // 🔍 Cek apakah data sudah ada
        $existing = $this->pembacaan625Model->where("id_pengukuran", $pengukuran_id)->first();

        $this->db->transStart();

        if ($existing) {
            // ✅ Update langsung tanpa syarat
            $this->pembacaan625Model
                ->where('id_pengukuran', $pengukuran_id)
                ->set($insertData)
                ->update();

            $msg = "Data ELV 625 berhasil diperbarui.";
        } else {
            // ✅ Insert baru kalau benar-benar belum ada
            $this->pembacaan625Model->insert($insertData);
            $msg = "Data ELV 625 berhasil disimpan.";
        }

        $this->db->transComplete();

        Events::trigger('dataElv625:insert', $pengukuran_id);

        return $this->response->setJSON([
            "status" => "success",
            "message" => $msg
        ]);

    } catch (\Exception $e) {
        $this->db->transRollback();
        log_message('error', '[saveElv625] Error: ' . $e->getMessage());
        return $this->response->setJSON([
            "status" => "error",
            "message" => "Terjadi kesalahan saat menyimpan data ELV 625: " . $e->getMessage()
        ]);
    }
}


    private function saveElv600($data, $pengukuran_id)
    {
        try {
            // Validasi pengukuran_id wajib ada
            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Silakan pilih data pengukuran terlebih dahulu!"
                ]);
            }

            // Ambil data ELV 600 yang sudah ada berdasarkan pengukuran_id
            $existing = $this->pembacaan600Model->where("id_pengukuran", $pengukuran_id)->first();

            // Field yang akan disimpan
            $fields = ['hv_1', 'hv_2', 'hv_3', 'hv_4', 'hv_5'];

            if ($existing) {
                $updateData = [];
                $infoMessages = [];

                foreach ($fields as $field) {
                    $newValue = $this->getVal($field, $data);

                    if ($newValue !== null) {
                        if ($existing[$field] === null) {
                            // Kolom kosong → update
                            $updateData[$field] = $newValue;
                        } else {
                            // Kolom sudah ada nilai → tidak update
                            $infoMessages[] = "Kolom {$field} sudah terisi, tidak diperbarui.";
                        }
                    }
                }

                if (!empty($updateData)) {
                    $this->db->transStart();

                    if (!$this->pembacaan600Model->update($existing['id_pembacaan'], $updateData)) {
                        $this->db->transRollback();
                        return $this->response->setJSON([
                            "status" => "error",
                            "message" => "Gagal memperbarui data ELV 600: " .
                                         implode(', ', $this->pembacaan600Model->errors())
                        ]);
                    }

                    $this->db->transComplete();

                    return $this->response->setJSON([
                        "status" => "success",
                        "message" => "Sebagian data ELV 600 berhasil diperbarui." .
                                     (!empty($infoMessages) ? " " . implode(' ', $infoMessages) : "")
                    ]);
                }

                return $this->response->setJSON([
                    "status" => "info",
                    "message" => "Tidak ada kolom yang diperbarui. " .
                                 (!empty($infoMessages) ? implode(' ', $infoMessages) : "Data ELV 600 sudah lengkap.")
                ]);
            }

            // Jika belum ada data → insert baru
            $insertData = ["id_pengukuran" => $pengukuran_id];
            $hasValue = false;

            foreach ($fields as $field) {
                $val = $this->getVal($field, $data);
                $insertData[$field] = $val;
                if ($val !== null) {
                    $hasValue = true;
                }
            }

            // Validasi minimal 1 kolom harus diisi
            if (!$hasValue) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Minimal satu nilai ELV 600 harus diisi!"
                ]);
            }

            $this->db->transStart();

            if (!$this->pembacaan600Model->insert($insertData)) {
                $this->db->transRollback();
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Gagal menyimpan data ELV 600: " .
                                 implode(', ', $this->pembacaan600Model->errors())
                ]);
            }

            Events::trigger('dataElv600:insert', $pengukuran_id);

            $this->db->transComplete();

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Data ELV 600 berhasil disimpan."
            ]);

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', '[saveElv600] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat menyimpan data ELV 600: " . $e->getMessage()
            ]);
        }
    }
}