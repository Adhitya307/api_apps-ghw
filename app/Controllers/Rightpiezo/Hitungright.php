<?php

namespace App\Controllers\Rightpiezo;

use CodeIgniter\Controller;
use Config\Database;
use App\Models\Rightpiezo\B_piezo_metrik;
use App\Models\Rightpiezo\T_pembacaan;
use App\Models\Rightpiezo\I_reading_atas;

class Hitungright extends Controller
{
    protected $db;
    protected $metrikModel;
    protected $pembacaanModel;
    protected $ireadingAtasModel;

    // Daftar lokasi untuk Right Piezo
    protected $lokasiList = [
        'R-01', 'R-02', 'R-03', 'R-04', 'R-05', 'R-06', 
        'R-07', 'R-08', 'R-09', 'R-10', 'R-11', 'R-12', 
        'IPZ-01', 'PZ-04'
    ];

    public function __construct()
    {
        $this->db = Database::connect('db_right_piez');
        $this->metrikModel = new B_piezo_metrik();
        $this->pembacaanModel = new T_pembacaan();
        $this->ireadingAtasModel = new I_reading_atas();

        // Support CORS untuk Android/Postman
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit;
        }
    }

    /**
     * =========================================================================
     * MAIN ENDPOINT - HITUNG SEMUA LOKASI SEKALIGUS
     * =========================================================================
     * 
     * Menghitung semua lokasi Right Piezo berdasarkan data pembacaan dan I-reading atas
     * Menggunakan rumus: =IF(H57="KERING";$BC$11;((H57*$AK$10)+(I57*$AL$10)))
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function hitungAll()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id diperlukan!"
                ]);
            }

            log_message('debug', "[Hitungright] Memulai perhitungan semua lokasi untuk pengukuran_id: $pengukuran_id");

            // 1. AMBIL DATA PEMBACAAN DARI t_pembacaan
            $dataPembacaan = $this->getDataPembacaan($pengukuran_id);
            if (empty($dataPembacaan)) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data pembacaan tidak ditemukan untuk pengukuran_id: $pengukuran_id"
                ]);
            }

            // 2. AMBIL DATA KEDALAMAN DARI i_reading_atas
            $dataIreading = $this->getDataIreadingAtas($pengukuran_id);
            if (empty($dataIreading)) {
                return $this->response->setJSON([
                    "status" => "error", 
                    "message" => "Data I-reading atas tidak ditemukan untuk pengukuran_id: $pengukuran_id"
                ]);
            }

            // 3. HITUNG SEMUA LOKASI MENGGUNAKAN MODEL
            $hasilPerhitungan = $this->metrikModel->hitungSemuaLokasi(
                $pengukuran_id,
                $dataPembacaan,
                $dataIreading
            );

            // 4. SIMPAN HASIL KE DATABASE
            $simpanBerhasil = $this->metrikModel->simpanHasilPerhitungan($pengukuran_id, $hasilPerhitungan);

            if (!$simpanBerhasil) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Gagal menyimpan hasil perhitungan ke database"
                ]);
            }

            // 5. FORMAT RESPONSE
            $response = $this->formatResponse($pengukuran_id, $hasilPerhitungan);

            log_message('debug', "[Hitungright] Perhitungan selesai untuk pengukuran_id: $pengukuran_id");

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            log_message('error', '[Hitungright] Error hitungAll: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan server: " . $e->getMessage()
            ]);
        }
    }

    /**
     * =========================================================================
     * ENDPOINT - HITUNG SATU LOKASI SPESIFIK
     * =========================================================================
     * 
     * Menghitung satu lokasi tertentu (R-01, R-02, ..., PZ-04)
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function hitungLokasi()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');
            $lokasi = $this->request->getGet('lokasi');

            if (!$pengukuran_id || !$lokasi) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id dan lokasi diperlukan!"
                ]);
            }

            // Validasi lokasi
            if (!in_array($lokasi, $this->lokasiList)) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Lokasi tidak valid. Pilih dari: " . implode(', ', $this->lokasiList)
                ]);
            }

            log_message('debug', "[Hitungright] Memulai perhitungan lokasi $lokasi untuk pengukuran_id: $pengukuran_id");

            // 1. AMBIL DATA PEMBACAAN UNTUK LOKASI INI
            $pembacaan = $this->pembacaanModel
                ->where('id_pengukuran', $pengukuran_id)
                ->where('lokasi', $lokasi)
                ->first();

            if (!$pembacaan) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data pembacaan untuk $lokasi tidak ditemukan!"
                ]);
            }

            // 2. AMBIL DATA KEDALAMAN UNTUK LOKASI INI
            $ireading = $this->ireadingAtasModel
                ->where('id_pengukuran', $pengukuran_id)
                ->where('titik_piezometer', $lokasi)
                ->first();

            if (!$ireading) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data I-reading atas untuk $lokasi tidak ditemukan!"
                ]);
            }

            // 3. VALIDASI DATA
            $errors = $this->metrikModel->validasiDataPerhitungan(
                $pengukuran_id,
                $lokasi,
                $pembacaan['feet'],
                $pembacaan['inch'],
                $ireading['kedalaman']
            );

            if (!empty($errors)) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data tidak valid: " . implode(', ', $errors)
                ]);
            }

            // 4. HITUNG DAN SIMPAN MENGGUNAKAN MODEL
            $result = $this->metrikModel->hitungDanSimpanLokasi(
                $pengukuran_id,
                $lokasi,
                $pembacaan['feet'],
                $pembacaan['inch'],
                $ireading['kedalaman']
            );

            log_message('debug', "[Hitungright] Perhitungan $lokasi selesai. Hasil: " . $result['hasil']);

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Perhitungan $lokasi berhasil",
                "data" => $result
            ]);

        } catch (\Exception $e) {
            log_message('error', "[Hitungright] Error hitungLokasi $lokasi: " . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan: " . $e->getMessage()
            ]);
        }
    }

    /**
     * =========================================================================
     * ENDPOINT - TRIGGER PERHITUNGAN OTOMATIS
     * =========================================================================
     * 
     * Dipanggil otomatis setelah simpan data pembacaan di Android
     * 
     * @param int $pengukuran_id
     * @param string $lokasi
     * @return \CodeIgniter\HTTP\Response
     */
    public function triggerHitung($pengukuran_id = null, $lokasi = null)
    {
        try {
            if (!$pengukuran_id) {
                $pengukuran_id = $this->request->getGet('pengukuran_id');
            }
            if (!$lokasi) {
                $lokasi = $this->request->getGet('lokasi');
            }

            if (!$pengukuran_id || !$lokasi) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id dan lokasi diperlukan!"
                ]);
            }

            log_message('debug', "[Hitungright] Trigger perhitungan untuk $lokasi, pengukuran_id: $pengukuran_id");

            // Panggil endpoint hitungLokasi
            return $this->hitungLokasi();

        } catch (\Exception $e) {
            log_message('error', "[Hitungright] Error triggerHitung: " . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Gagal trigger perhitungan: " . $e->getMessage()
            ]);
        }
    }

    /**
     * =========================================================================
     * ENDPOINT - CEK STATUS PERHITUNGAN
     * =========================================================================
     * 
     * Melihat status perhitungan untuk semua lokasi
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function getStatus()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id diperlukan!"
                ]);
            }

            $status = [];
            $totalDataPembacaan = 0;
            $totalDataIreading = 0;
            $totalSudahDihitung = 0;

            foreach ($this->lokasiList as $lokasi) {
                // Cek data pembacaan
                $pembacaan = $this->pembacaanModel
                    ->where('id_pengukuran', $pengukuran_id)
                    ->where('lokasi', $lokasi)
                    ->first();

                // Cek data I-reading atas
                $ireading = $this->ireadingAtasModel
                    ->where('id_pengukuran', $pengukuran_id)
                    ->where('titik_piezometer', $lokasi)
                    ->first();

                // Cek hasil perhitungan
                $metrik = $this->metrikModel->find($pengukuran_id);
                $hasil = $metrik[$lokasi] ?? null;
                $sudahDihitung = !empty($hasil);

                $status[$lokasi] = [
                    'pembacaan_ada' => !empty($pembacaan),
                    'ireading_ada' => !empty($ireading),
                    'sudah_dihitung' => $sudahDihitung,
                    'hasil' => $hasil,
                    'feet_input' => $pembacaan['feet'] ?? null,
                    'inch_input' => $pembacaan['inch'] ?? null,
                    'kedalaman' => $ireading['kedalaman'] ?? null
                ];

                if (!empty($pembacaan)) $totalDataPembacaan++;
                if (!empty($ireading)) $totalDataIreading++;
                if ($sudahDihitung) $totalSudahDihitung++;
            }

            return $this->response->setJSON([
                "status" => "success",
                "data" => [
                    'pengukuran_id' => $pengukuran_id,
                    'total_lokasi' => count($this->lokasiList),
                    'statistik' => [
                        'data_pembacaan' => $totalDataPembacaan,
                        'data_ireading' => $totalDataIreading,
                        'sudah_dihitung' => $totalSudahDihitung,
                        'belum_dihitung' => count($this->lokasiList) - $totalSudahDihitung
                    ],
                    'detail' => $status
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', '[Hitungright] Error getStatus: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan: " . $e->getMessage()
            ]);
        }
    }

    /**
     * =========================================================================
     * ENDPOINT - GET HASIL PERHITUNGAN
     * =========================================================================
     * 
     * Mengambil hasil perhitungan yang sudah disimpan
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function getHasil()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id diperlukan!"
                ]);
            }

            $metrik = $this->metrikModel->find($pengukuran_id);

            if (!$metrik) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data hasil perhitungan tidak ditemukan!"
                ]);
            }

            // Format hasil
            $hasil = [];
            foreach ($this->lokasiList as $lokasi) {
                $hasil[$lokasi] = $metrik[$lokasi] ?? null;
            }

            return $this->response->setJSON([
                "status" => "success",
                "data" => [
                    'pengukuran_id' => $pengukuran_id,
                    'feet_konversi' => $metrik['feet'] ?? 0.3048,
                    'inch_konversi' => $metrik['inch'] ?? 0.0254,
                    'hasil_perhitungan' => $hasil
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', '[Hitungright] Error getHasil: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan: " . $e->getMessage()
            ]);
        }
    }

    /**
     * =========================================================================
     * HELPER METHODS
     * =========================================================================
     */

    /**
     * Ambil data pembacaan dari tabel t_pembacaan
     */
    private function getDataPembacaan($pengukuran_id)
    {
        $pembacaanData = $this->pembacaanModel
            ->where('id_pengukuran', $pengukuran_id)
            ->findAll();

        $formattedData = [];
        foreach ($pembacaanData as $data) {
            $formattedData[$data['lokasi']] = [
                'feet' => $data['feet'],
                'inch' => $data['inch']
            ];
        }

        return $formattedData;
    }

    /**
     * Ambil data kedalaman dari tabel i_reading_atas
     */
    private function getDataIreadingAtas($pengukuran_id)
    {
        $ireadingData = $this->ireadingAtasModel
            ->where('id_pengukuran', $pengukuran_id)
            ->findAll();

        $formattedData = [];
        foreach ($ireadingData as $data) {
            $formattedData[$data['titik_piezometer']] = $data['kedalaman'];
        }

        return $formattedData;
    }

    /**
     * Format response untuk hitungAll
     */
    private function formatResponse($pengukuran_id, $hasilPerhitungan)
    {
        $totalBerhasil = 0;
        $detail = [];

        foreach ($hasilPerhitungan as $lokasi => $hasil) {
            $detail[$lokasi] = [
                'status' => 'success',
                'hasil' => $hasil['hasil'],
                'feet_input' => $hasil['feet_input'],
                'inch_input' => $hasil['inch_input'],
                'kedalaman' => $hasil['kedalaman'],
                'rumus_terpakai' => $hasil['rumus_terpakai']
            ];
            $totalBerhasil++;
        }

        return [
            "status" => "success",
            "message" => "Perhitungan selesai. $totalBerhasil dari " . count($this->lokasiList) . " lokasi berhasil dihitung.",
            "data" => [
                'pengukuran_id' => $pengukuran_id,
                'total_lokasi' => count($this->lokasiList),
                'total_berhasil' => $totalBerhasil,
                'detail' => $detail
            ]
        ];
    }

    /**
     * =========================================================================
     * ENDPOINT - TEST RUMUS
     * =========================================================================
     * 
     * Untuk testing rumus perhitungan tanpa menyimpan ke database
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function testRumus()
    {
        try {
            $feet = $this->request->getGet('feet');
            $inch = $this->request->getGet('inch');
            $kedalaman = $this->request->getGet('kedalaman');

            if ($feet === null || $inch === null || $kedalaman === null) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter feet, inch, dan kedalaman diperlukan!"
                ]);
            }

            // Test rumus
            $hasil = $this->metrikModel->hitungRumusPiezo($feet, $inch, $kedalaman);

            return $this->response->setJSON([
                "status" => "success",
                "data" => [
                    'feet_input' => $feet,
                    'inch_input' => $inch,
                    'kedalaman' => $kedalaman,
                    'hasil_perhitungan' => $hasil,
                    'rumus_terpakai' => $this->metrikModel->getRumusTerpakai($feet),
                    'keterangan' => $feet === 'KERING' ? 
                        'Menggunakan kedalaman langsung (kondisi KERING)' : 
                        'Menggunakan rumus: (feet × 0.3048) + (inch × 0.0254)'
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', '[Hitungright] Error testRumus: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan: " . $e->getMessage()
            ]);
        }
    }
}