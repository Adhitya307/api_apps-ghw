<?php

namespace App\Controllers\Leftpiez;

use CodeIgniter\Controller;
use Config\Database;

class GetPengukuranLeftpiez extends Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect('db_left_piez'); // Gunakan koneksi db_left_piez
        
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

            $query = $this->db->table("t_pengukuran_leftpiez")
                ->select("id_pengukuran, tahun, periode, tanggal, dma, temp_id")
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
            log_message('error', '[GetPengukuranLeftpiez] Error: ' . $e->getMessage());
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
            $query = $this->db->table("t_pengukuran_leftpiez")
                ->select("id_pengukuran, tahun, periode, tanggal, dma, temp_id")
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
            log_message('error', '[GetPengukuranLeftpiez] Error: ' . $e->getMessage());
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

            $query = $this->db->table("t_pengukuran_leftpiez")
                ->select("id_pengukuran, tahun, periode, tanggal, dma, temp_id");

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
            log_message('error', '[GetPengukuranLeftpiez] Error: ' . $e->getMessage());
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

            $query = $this->db->table("t_pengukuran_leftpiez")
                ->select("id_pengukuran, tahun, periode, tanggal, dma, temp_id, created_at, updated_at")
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
            log_message('error', '[GetPengukuranLeftpiez] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data: " . $e->getMessage()
            ]);
        }
    }
}