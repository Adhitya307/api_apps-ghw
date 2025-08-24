<?php

namespace App\Controllers\Rembesan;

use App\Controllers\BaseController;
use App\Models\Rembesan\TotalBocoranModel;

class TotalBocoranController extends BaseController
{
    protected $totalBocoranModel;
    protected $db;

    public function __construct()
    {
        $this->totalBocoranModel = new TotalBocoranModel();
        $this->db = \Config\Database::connect();
        helper('totalbocoran'); // pastikan helper sudah ada
    }

    /**
     * Proses perhitungan Total Bocoran (R1)
     *
     * @param int $pengukuran_id
     * @return array|false
     */
    public function proses($pengukuran_id)
    {
        try {
            // 🔹 Ambil data A1 dari p_intigalery
            $a1Row = $this->db->table('p_intigalery')
                ->select('a1')
                ->where('pengukuran_id', $pengukuran_id)
                ->get()->getRow();

            // 🔹 Ambil data B3 dari p_spillway
            $b3Row = $this->db->table('p_spillway')
                ->select('b3')
                ->where('pengukuran_id', $pengukuran_id)
                ->get()->getRow();

            // 🔹 Ambil data SR dari p_tebingkanan
            $srRow = $this->db->table('p_tebingkanan')
                ->select('sr')
                ->where('pengukuran_id', $pengukuran_id)
                ->get()->getRow();

            if (!$a1Row || !$b3Row || !$srRow) {
                log_message('error', "[TotalBocoran] ❌ Data belum lengkap untuk ID {$pengukuran_id}");
                return false;
            }

            $a1 = (float) $a1Row->a1;
            $b3 = (float) $b3Row->b3;
            $sr = (float) $srRow->sr;

            // 🔹 Hitung total R1 dengan helper
            $r1 = hitungTotalBocoran($a1, $b3, $sr);

            // 🔹 Simpan ke DB (update kalau sudah ada)
            $existing = $this->totalBocoranModel
                ->where('pengukuran_id', $pengukuran_id)
                ->first();

            if ($existing) {
                $this->totalBocoranModel->update($existing['id'], [
                    'R1' => $r1,
                ]);
                log_message('debug', "[TotalBocoran] 🔄 Update R1={$r1} untuk ID {$pengukuran_id}");
            } else {
                $this->totalBocoranModel->insert([
                    'pengukuran_id' => $pengukuran_id,
                    'R1' => $r1,
                ]);
                log_message('debug', "[TotalBocoran] ➕ Insert R1={$r1} untuk ID {$pengukuran_id}");
            }

            return [
                'success' => true,
                'pengukuran_id' => $pengukuran_id,
                'R1' => $r1
            ];

        } catch (\Exception $e) {
            log_message('error', "[TotalBocoran] Exception: " . $e->getMessage());
            return false;
        }
    }
}
