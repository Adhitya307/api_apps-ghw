<?php
namespace App\Controllers\Rembesan;

use CodeIgniter\Controller;
use Config\Database;
use App\Models\Rembesan\MDataPengukuran;
use App\Models\Rembesan\MThomsonWeir;
use App\Models\Rembesan\MSR;
use App\Models\Rembesan\MBocoranBaru;
use CodeIgniter\Events\Events;

class InputRembesan extends Controller
{
    protected $db;
    protected $pengukuranModel;
    protected $thomsonModel;
    protected $srModel;
    protected $bocoranModel;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->pengukuranModel = new MDataPengukuran();
        $this->thomsonModel = new MThomsonWeir();
        $this->srModel = new MSR();
        $this->bocoranModel = new MBocoranBaru();

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

            log_message('debug', "[InputRembesan] mode={$mode}, pengukuran_id={$pengukuran_id}, temp_id={$temp_id}");

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
                $row = $this->db->table("t_data_pengukuran")
                    ->where("temp_id", $temp_id)
                    ->get()
                    ->getRow();
                if ($row) {
                    $pengukuran_id = $row->id;
                    log_message('debug', "[InputRembesan] pengukuran_id ditemukan dari temp_id={$temp_id} → id={$pengukuran_id}");
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
                    "message" => "Data pengukuran dengan ID $pengukuran_id tidak ditemukan di tabel t_data_pengukuran!"
                ]);
            }

            switch ($mode) {
                case "thomson":
                    return $this->saveThomson($data, $pengukuran_id);
                case "sr":
                    return $this->saveSr($data, $pengukuran_id);
                case "bocoran":
                    return $this->saveBocoran($data, $pengukuran_id);
                default:
                    return $this->response->setJSON([
                        "status" => "error",
                        "message" => "Mode tidak dikenali: $mode"
                    ]);
            }
        } catch (\Exception $e) {
            log_message('error', '[InputRembesan] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan server: " . $e->getMessage()
            ]);
        }
    }

    private function savePengukuran($data, $temp_id)
{
    try {
        $pengukuran_id = $this->getVal('pengukuran_id', $data); // ID yang dipilih
        $tahun   = $this->getVal('tahun', $data);
        $bulan   = $this->getVal('bulan', $data);
        $periode = $this->getVal('periode', $data);
        $tanggal = $this->getVal('tanggal', $data);
        $tma     = $this->getVal('tma_waduk', $data);
        $curah   = $this->getVal('curah_hujan', $data);

        // ✅ STEP 1: Jika ada pengukuran_id → update jika TMA NULL
        if ($pengukuran_id) {
            $check = $this->pengukuranModel->find($pengukuran_id);

            if (!$check) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data pengukuran tidak ditemukan!"
                ]);
            }

            if ($tma !== null) {
                if ($check['tma_waduk'] === null) {
                    $this->pengukuranModel->update($pengukuran_id, ['tma_waduk' => $tma]);

                    return $this->response->setJSON([
                        "status" => "success",
                        "message" => "TMA Waduk berhasil diperbarui.",
                        "pengukuran_id" => $pengukuran_id
                    ]);
                } else {
                    return $this->response->setJSON([
                        "status" => "info",
                        "message" => "TMA Waduk sudah ada, tidak diperbarui.",
                        "pengukuran_id" => $pengukuran_id
                    ]);
                }
            }

            return $this->response->setJSON([
                "status" => "error",
                "message" => "Tidak ada nilai TMA yang dikirim!"
            ]);
        }

        // ✅ STEP 2: Jika tidak ada pengukuran_id → wajib tahun & tanggal
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

// ✅ Cek apakah data sudah ada berdasarkan tahun & bulan SAJA
$check = $this->db->table("t_data_pengukuran")
    ->where("tahun", $tahun)
    ->where("tanggal", $tanggal)
    //->where("bulan", $bulan)
    // ->where("periode", $periode) ❌ dihapus agar periode sama tidak dianggap duplikat
    ->get()
    ->getRow();

if ($check) {
    if ($tma !== null) {
        if ($check->tma_waduk === null) {
            $this->db->table("t_data_pengukuran")
                ->where("id", $check->id)
                ->update(["tma_waduk" => $tma]);

            return $this->response->setJSON([
                "status" => "success",
                "message" => "TMA Waduk berhasil diperbarui.",
                "pengukuran_id" => $check->id
            ]);
        } else {
            return $this->response->setJSON([
                "status" => "info",
                "message" => "TMA Waduk sudah ada, tidak diperbarui.",
                "pengukuran_id" => $check->id
            ]);
        }
    }

    return $this->response->setJSON([
        "status" => "info",
        "message" => "Data pengukuran sudah ada.",
        "pengukuran_id" => $check->id
    ]);
}


        // ✅ STEP 3: Insert baru jika belum ada data
        $insertData = [
            "tahun" => $tahun,
            "bulan" => $bulan,
            "periode" => $periode,
            "tanggal" => $tanggal,
            "tma_waduk" => $tma,
            "curah_hujan" => $curah,
            "temp_id" => $temp_id
        ];

        $this->db->transStart();

        if (!$this->pengukuranModel->insert($insertData)) {
            $this->db->transRollback();
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal menyimpan data pengukuran: " .
                    implode(', ', $this->pengukuranModel->errors())
            ]);
        }

        $pengukuran_id = $this->pengukuranModel->getInsertID();

        Events::trigger('dataPengukuran:insert', $pengukuran_id);

        $this->db->transComplete();

        return $this->response->setJSON([
            "status" => "success",
            "message" => "Data pengukuran berhasil disimpan.",
            "pengukuran_id" => $pengukuran_id
        ]);

    } catch (\Exception $e) {
        $this->db->transRollback();
        log_message('error', '[savePengukuran] Error: ' . $e->getMessage());
        return $this->response->setJSON([
            "status" => "error",
            "message" => "Terjadi kesalahan saat menyimpan data pengukuran: " . $e->getMessage()
        ]);
    }
}

   private function saveThomson($data, $pengukuran_id)
{
    try {
        // Validasi pengukuran_id wajib ada
        if (!$pengukuran_id) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Silakan pilih data pengukuran terlebih dahulu!"
            ]);
        }

        // Ambil data thomson yang sudah ada berdasarkan pengukuran_id
        $existing = $this->thomsonModel->where("pengukuran_id", $pengukuran_id)->first();

        // Field yang akan disimpan
        $fields = ['a1_r', 'a1_l', 'b1', 'b3', 'b5'];

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

                if (!$this->thomsonModel->update($existing['id'], $updateData)) {
                    $this->db->transRollback();
                    return $this->response->setJSON([
                        "status" => "error",
                        "message" => "Gagal memperbarui data Thomson Weir: " .
                                     implode(', ', $this->thomsonModel->errors())
                    ]);
                }

                $this->db->transComplete();

                return $this->response->setJSON([
                    "status" => "success",
                    "message" => "Sebagian data Thomson berhasil diperbarui." .
                                 (!empty($infoMessages) ? " " . implode(' ', $infoMessages) : "")
                ]);
            }

            return $this->response->setJSON([
                "status" => "info",
                "message" => "Tidak ada kolom yang diperbarui. " .
                             (!empty($infoMessages) ? implode(' ', $infoMessages) : "Data Thomson sudah lengkap.")
            ]);
        }

        // Jika belum ada data → insert baru
        $insertData = ["pengukuran_id" => $pengukuran_id];
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
                "message" => "Minimal satu nilai Thomson Weir harus diisi!"
            ]);
        }

        $this->db->transStart();

        if (!$this->thomsonModel->insert($insertData)) {
            $this->db->transRollback();
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal menyimpan data Thomson Weir: " .
                             implode(', ', $this->thomsonModel->errors())
            ]);
        }

        Events::trigger('dataThomson:insert', $pengukuran_id);

        $this->db->transComplete();

        return $this->response->setJSON([
            "status" => "success",
            "message" => "Data Thomson Weir berhasil disimpan."
        ]);

    } catch (\Exception $e) {
        $this->db->transRollback();
        log_message('error', '[saveThomson] Error: ' . $e->getMessage());
        return $this->response->setJSON([
            "status" => "error",
            "message" => "Terjadi kesalahan saat menyimpan data Thomson: " . $e->getMessage()
        ]);
    }
}

    private function saveSr($data, $pengukuran_id)
    {
        try {
            $check = $this->srModel->where("pengukuran_id", $pengukuran_id)->first();
            if ($check) {
                return $this->response->setJSON([
                    "status" => "success",
                    "message" => "Data SR sudah ada."
                ]);
            }

            $fields = [1,40,66,68,70,79,81,83,85,92,94,96,98,100,102,104,106];
            $insertData = ["pengukuran_id" => $pengukuran_id];

            foreach ($fields as $kode) {
                $insertData["sr_{$kode}_kode"] = $this->getVal("sr_{$kode}_kode", $data) ?? '';
                $insertData["sr_{$kode}_nilai"] = floatval($this->getVal("sr_{$kode}_nilai", $data) ?? 0);
            }

            $this->db->transStart();
            
            if (!$this->srModel->insert($insertData)) {
                $this->db->transRollback();
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Gagal menyimpan data SR: " . 
                                implode(', ', $this->srModel->errors())
                ]);
            }

            // Trigger SR (berdiri sendiri)
Events::trigger('dataSR:insert', $pengukuran_id);

            $this->db->transComplete();

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Data SR berhasil disimpan."
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', '[saveSr] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan saat menyimpan data SR: " . $e->getMessage()
            ]);
        }
    }

    private function saveBocoran($data, $pengukuran_id)
{
    try {
        $check = $this->bocoranModel->where("pengukuran_id", $pengukuran_id)->first();
        if ($check) {
            return $this->response->setJSON([
                "status" => "success",
                "message" => "Data bocoran sudah ada."
            ]);
        }

        $bocoranData = [
            "pengukuran_id" => $pengukuran_id,
            "elv_624_t1" => $this->getVal('elv_624_t1', $data),
            "elv_624_t1_kode" => $this->getVal('elv_624_t1_kode', $data),
            "elv_615_t2" => $this->getVal('elv_615_t2', $data),
            "elv_615_t2_kode" => $this->getVal('elv_615_t2_kode', $data),
            "pipa_p1" => $this->getVal('pipa_p1', $data),
            "pipa_p1_kode" => $this->getVal('pipa_p1_kode', $data),
        ];

        $this->db->transStart();

        if (!$this->bocoranModel->insert($bocoranData)) {
            $this->db->transRollback();
            log_message('error', '[saveBocoran] Gagal insert: ' . json_encode($this->bocoranModel->errors()));
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal menyimpan data bocoran"
            ]);
        }

        $this->db->transComplete(); // ✅ Commit transaksi

        // ✅ Panggil event setelah commit
        Events::trigger('dataPengukuran:insert', $pengukuran_id);

        return $this->response->setJSON([
            "status" => "success",
            "message" => "Data bocoran berhasil disimpan."
        ]);
    } catch (\Exception $e) {
        $this->db->transRollback();
        log_message('error', '[saveBocoran] Error: ' . $e->getMessage());
        return $this->response->setJSON([
            "status" => "error",
            "message" => "Terjadi kesalahan saat menyimpan data bocoran: " . $e->getMessage()
        ]);
    }
}
}