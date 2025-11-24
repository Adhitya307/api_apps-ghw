<?php

namespace App\Controllers\Rightpiezo;

use CodeIgniter\Controller;
use Config\Database;

class GetPengukuranRightpiez extends Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect('db_right_piez'); // Gunakan koneksi db_right_piez
        
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        
        // Handle preflight request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit;
        }
    }

    public function index()
    {
        try {
            $bulanIni = date('m'); // bulan saat ini, 01-12
            $tahunIni = date('Y'); // tahun saat ini, misal 2025

            $query = $this->db->table("t_pengukuran_rightpiez")
                ->select("id_pengukuran, tahun, periode, tanggal, tma, ch_hujan, temp_id")
                ->where("MONTH(tanggal)", $bulanIni)
                ->where("YEAR(tanggal)", $tahunIni)
                ->orderBy("tanggal", "DESC")
                ->orderBy("tahun", "DESC")
                ->orderBy("periode", "DESC")
                ->get();
            
            $data = $query->getResultArray();

            return $this->response->setJSON([
                "status" => "success",
                "data" => $data
            ]);

        } catch (\Exception $e) {
            log_message('error', '[GetPengukuranRightpiez] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method tambahan untuk mendapatkan semua data pengukuran (tanpa filter bulan)
     */
    public function getAll()
    {
        try {
            $query = $this->db->table("t_pengukuran_rightpiez")
                ->select("id_pengukuran, tahun, periode, tanggal, tma, ch_hujan, temp_id")
                ->orderBy("tanggal", "DESC")
                ->orderBy("tahun", "DESC")
                ->orderBy("periode", "DESC")
                ->get();
            
            $data = $query->getResultArray();

            return $this->response->setJSON([
                "status" => "success",
                "data" => $data
            ]);

        } catch (\Exception $e) {
            log_message('error', '[GetPengukuranRightpiez] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data pengukuran berdasarkan tahun dan bulan
     */
    public function getByPeriod()
    {
        try {
            $tahun = $this->request->getGet('tahun');
            $bulan = $this->request->getGet('bulan');

            $query = $this->db->table("t_pengukuran_rightpiez")
                ->select("id_pengukuran, tahun, periode, tanggal, tma, ch_hujan, temp_id");

            if ($tahun) {
                $query->where("YEAR(tanggal)", $tahun);
            }

            if ($bulan) {
                $query->where("MONTH(tanggal)", $bulan);
            }

            $query->orderBy("tanggal", "DESC")
                  ->orderBy("tahun", "DESC")
                  ->orderBy("periode", "DESC");
            
            $data = $query->get()->getResultArray();

            return $this->response->setJSON([
                "status" => "success",
                "data" => $data
            ]);

        } catch (\Exception $e) {
            log_message('error', '[GetPengukuranRightpiez] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan detail satu pengukuran berdasarkan ID
     */
    public function getById($id = null)
    {
        try {
            if (!$id) {
                $id = $this->request->getGet('id_pengukuran');
            }

            if (!$id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "ID pengukuran harus diisi!"
                ]);
            }

            $query = $this->db->table("t_pengukuran_rightpiez")
                ->select("id_pengukuran, tahun, periode, tanggal, tma, ch_hujan, temp_id")
                ->where("id_pengukuran", $id)
                ->get();
            
            $data = $query->getRowArray();

            if (!$data) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data pengukuran tidak ditemukan!"
                ]);
            }

            return $this->response->setJSON([
                "status" => "success",
                "data" => $data
            ]);

        } catch (\Exception $e) {
            log_message('error', '[GetPengukuranRightpiez] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data pembacaan berdasarkan ID pengukuran
     */
    public function getPembacaanByPengukuranId()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id harus diisi!"
                ]);
            }

            $query = $this->db->table("t_pembacaan")
                ->select("id_bacaan, id_pengukuran, lokasi, feet, inch")
                ->where("id_pengukuran", $pengukuran_id)
                ->orderBy("lokasi", "ASC")
                ->get();
            
            $data = $query->getResultArray();

            return $this->response->setJSON([
                "status" => "success",
                "data" => $data
            ]);

        } catch (\Exception $e) {
            log_message('error', '[GetPengukuranRightpiez] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data pembacaan: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data pembacaan berdasarkan ID pengukuran dan lokasi
     */
    public function getPembacaanByLokasi()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');
            $lokasi = $this->request->getGet('lokasi');

            if (!$pengukuran_id || !$lokasi) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id dan lokasi harus diisi!"
                ]);
            }

            $query = $this->db->table("t_pembacaan")
                ->select("id_bacaan, id_pengukuran, lokasi, feet, inch")
                ->where("id_pengukuran", $pengukuran_id)
                ->where("lokasi", $lokasi)
                ->get();
            
            $data = $query->getRowArray();

            return $this->response->setJSON([
                "status" => "success",
                "data" => $data ?: []
            ]);

        } catch (\Exception $e) {
            log_message('error', '[GetPengukuranRightpiez] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data pembacaan: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data metrik berdasarkan ID pengukuran
     */
    public function getMetrikByPengukuranId()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id harus diisi!"
                ]);
            }

            $query = $this->db->table("b_piezo_metrik")
                ->select("*")
                ->where("id_pengukuran", $pengukuran_id)
                ->get();
            
            $data = $query->getRowArray();

            return $this->response->setJSON([
                "status" => "success",
                "data" => $data ?: []
            ]);

        } catch (\Exception $e) {
            log_message('error', '[GetPengukuranRightpiez] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data metrik: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data I-reading atas berdasarkan ID pengukuran
     */
    public function getIreadingAtasByPengukuranId()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id harus diisi!"
                ]);
            }

            $query = $this->db->table("I_reading_atas")
                ->select("*")
                ->where("id_pengukuran", $pengukuran_id)
                ->get();
            
            $data = $query->getRowArray();

            return $this->response->setJSON([
                "status" => "success",
                "data" => $data ?: []
            ]);

        } catch (\Exception $e) {
            log_message('error', '[GetPengukuranRightpiez] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data I-reading atas: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data perhitungan tengah berdasarkan ID pengukuran
     */
    public function getPerhitunganTengahByPengukuranId()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id harus diisi!"
                ]);
            }

            $query = $this->db->table("perhitungan_tengah")
                ->select("*")
                ->where("id_pengukuran", $pengukuran_id)
                ->get();
            
            $data = $query->getRowArray();

            return $this->response->setJSON([
                "status" => "success",
                "data" => $data ?: []
            ]);

        } catch (\Exception $e) {
            log_message('error', '[GetPengukuranRightpiez] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data perhitungan tengah: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data elevasi dasar berdasarkan ID pengukuran
     */
    public function getElevasiDasarByPengukuranId()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id harus diisi!"
                ]);
            }

            $query = $this->db->table("elevasi_dasar")
                ->select("*")
                ->where("id_pengukuran", $pengukuran_id)
                ->get();
            
            $data = $query->getRowArray();

            return $this->response->setJSON([
                "status" => "success",
                "data" => $data ?: []
            ]);

        } catch (\Exception $e) {
            log_message('error', '[GetPengukuranRightpiez] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data elevasi dasar: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan semua data terkait satu pengukuran (complete data)
     */
    public function getCompleteDataByPengukuranId()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id harus diisi!"
                ]);
            }

            // Data pengukuran utama
            $pengukuran = $this->db->table("t_pengukuran_rightpiez")
                ->where("id_pengukuran", $pengukuran_id)
                ->get()
                ->getRowArray();

            if (!$pengukuran) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data pengukuran tidak ditemukan!"
                ]);
            }

            // Data pembacaan
            $pembacaan = $this->db->table("t_pembacaan")
                ->where("id_pengukuran", $pengukuran_id)
                ->orderBy("lokasi", "ASC")
                ->get()
                ->getResultArray();

            // Data lainnya
            $metrik = $this->db->table("b_piezo_metrik")
                ->where("id_pengukuran", $pengukuran_id)
                ->get()
                ->getRowArray();

            $ireading_atas = $this->db->table("I_reading_atas")
                ->where("id_pengukuran", $pengukuran_id)
                ->get()
                ->getRowArray();

            $perhitungan_tengah = $this->db->table("perhitungan_tengah")
                ->where("id_pengukuran", $pengukuran_id)
                ->get()
                ->getRowArray();

            $elevasi_dasar = $this->db->table("elevasi_dasar")
                ->where("id_pengukuran", $pengukuran_id)
                ->get()
                ->getRowArray();

            $completeData = [
                "pengukuran" => $pengukuran,
                "pembacaan" => $pembacaan,
                "metrik" => $metrik ?: [],
                "ireading_atas" => $ireading_atas ?: [],
                "perhitungan_tengah" => $perhitungan_tengah ?: [],
                "elevasi_dasar" => $elevasi_dasar ?: []
            ];

            return $this->response->setJSON([
                "status" => "success",
                "data" => $completeData
            ]);

        } catch (\Exception $e) {
            log_message('error', '[GetPengukuranRightpiez] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data lengkap: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Method untuk mendapatkan data berdasarkan temp_id
     */
    public function getByTempId()
    {
        try {
            $temp_id = $this->request->getGet('temp_id');

            if (!$temp_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter temp_id harus diisi!"
                ]);
            }

            $query = $this->db->table("t_pengukuran_rightpiez")
                ->select("id_pengukuran, tahun, periode, tanggal, tma, ch_hujan, temp_id")
                ->where("temp_id", $temp_id)
                ->get();
            
            $data = $query->getRowArray();

            if (!$data) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data pengukuran dengan temp_id " . $temp_id . " tidak ditemukan!"
                ]);
            }

            return $this->response->setJSON([
                "status" => "success",
                "data" => $data
            ]);

        } catch (\Exception $e) {
            log_message('error', '[GetPengukuranRightpiez] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data: " . $e->getMessage()
            ]);
        }
    }
}