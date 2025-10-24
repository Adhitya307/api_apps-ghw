<?php
namespace App\Controllers\DomBody;

use CodeIgniter\Controller;
use Config\Database;
use App\Models\DomBody\MPergerakanElv600;
use App\Models\DomBody\MPergerakanElv625;

class Hitungpergerakan extends Controller
{
    protected $db;
    protected $pergerakan600;
    protected $pergerakan625;

    public function __construct()
    {
        $this->db = Database::connect('hdm');
        $this->pergerakan600 = new MPergerakanElv600();
        $this->pergerakan625 = new MPergerakanElv625();

        // ✅ CORS untuk Postman / Android
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit;
        }
    }

    /**
     * Fungsi bantu: ambil nilai dengan aman dari input array
     */
    private function getVal($key, $data)
    {
        return isset($data[$key]) && trim($data[$key]) !== '' ? trim($data[$key]) : null;
    }

    /**
     * =====================================================
     * 🔹 Hitung Pergerakan ELV 600
     * =====================================================
     */
    public function hitungElv600()
    {
        try {
            // Baca input (JSON atau form POST)
            $rawInput = $this->request->getBody();
            $data = json_decode($rawInput, true);
            if (!$data || json_last_error() !== JSON_ERROR_NONE) {
                $data = $this->request->getPost();
            }

            // Ambil ID pengukuran
            $pengukuran_id = $this->getVal('pengukuran_id', $data);

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Parameter pengukuran_id wajib dikirim!'
                ]);
            }

            // ✅ Jalankan perhitungan di model
            $hasil = $this->pergerakan600->hitungPergerakan($pengukuran_id);

            if ($hasil['status'] === 'error') {
                return $this->response->setJSON($hasil);
            }

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Pergerakan ELV600 berhasil dihitung dan disimpan.',
                'data'    => $hasil['data']
            ]);

        } catch (\Exception $e) {
            log_message('error', '[Hitungpergerakan::hitungElv600] ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat menghitung pergerakan ELV600: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * =====================================================
     * 🔹 Hitung Pergerakan ELV 625
     * =====================================================
     */
    public function hitungElv625()
    {
        try {
            // Baca input (JSON atau form POST)
            $rawInput = $this->request->getBody();
            $data = json_decode($rawInput, true);
            if (!$data || json_last_error() !== JSON_ERROR_NONE) {
                $data = $this->request->getPost();
            }

            // Ambil ID pengukuran
            $pengukuran_id = $this->getVal('pengukuran_id', $data);

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Parameter pengukuran_id wajib dikirim!'
                ]);
            }

            // ✅ Jalankan perhitungan di model
            $hasil = $this->pergerakan625->hitungPergerakan($pengukuran_id);

            if ($hasil['status'] === 'error') {
                return $this->response->setJSON($hasil);
            }

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Pergerakan ELV625 berhasil dihitung dan disimpan.',
                'data'    => $hasil['data']
            ]);

        } catch (\Exception $e) {
            log_message('error', '[Hitungpergerakan::hitungElv625] ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat menghitung pergerakan ELV625: ' . $e->getMessage()
            ]);
        }
    }
}
