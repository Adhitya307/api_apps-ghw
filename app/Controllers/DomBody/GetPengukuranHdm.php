<?php
namespace App\Controllers\DomBody;

use CodeIgniter\Controller;
use Config\Database;

class GetPengukuranHdm extends Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect('hdm'); // Gunakan koneksi HDM
        
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

            $query = $this->db->table("t_pengukuran_hdm")
                ->select("id_pengukuran as id, tanggal")
                ->where("MONTH(tanggal)", $bulanIni)
                ->where("YEAR(tanggal)", $tahunIni)
                ->orderBy("tanggal", "DESC")
                ->get();
            
            $data = $query->getResultArray();

            return $this->response->setJSON([
                "status" => "success",
                "data" => $data
            ]);

        } catch (\Exception $e) {
            log_message('error', '[GetPengukuranHdm] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal mengambil data: " . $e->getMessage()
            ]);
        }
    }
}