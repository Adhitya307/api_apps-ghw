<?php

namespace App\Controllers\Btm;

use CodeIgniter\Controller;
use Config\Database;

class GetPengukuranBtm extends Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect('btm'); // Gunakan koneksi BTM
        
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

            $query = $this->db->table("t_pengukuran_btm")
                ->select("id_pengukuran, tahun, periode, tanggal, temp_id")
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
            log_message('error', '[GetPengukuranBtm] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data: " . $e->getMessage()
            ]);
        }
    }
}