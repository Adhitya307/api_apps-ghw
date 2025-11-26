<?php

namespace App\Controllers\Rightpiezo;

use CodeIgniter\Controller;
use Config\Database;
use App\Models\Rightpiezo\B_piezo_metrik;
use App\Models\Rightpiezo\T_pembacaan;
use App\Models\Rightpiezo\I_reading_atas;
use App\Models\Rightpiezo\Perhitungan_t_psmetrik;

class Hitungright extends Controller
{
    protected $db;
    protected $metrikModel;
    protected $pembacaanModel;
    protected $ireadingAtasModel;
    protected $perhitunganPsmetrikModel;

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
        $this->perhitunganPsmetrikModel = new Perhitungan_t_psmetrik();

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
     * MAIN ENDPOINT - HITUNG SEMUA LOKASI SEKALIGUS (2 TAHAP)
     * =========================================================================
     * 
     * TAHAP 1: Hitung B_piezo_metrik menggunakan rumus: =IF(H57="KERING";$BC$11;((H57*$AK$10)+(I57*$AL$10)))
     * TAHAP 2: Hitung Perhitungan_t_psmetrik menggunakan rumus: Elv_Piez - Hasil_B_Metrik
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

            log_message('debug', "[Hitungright] Memulai perhitungan 2 tahap untuk pengukuran_id: $pengukuran_id");

            // ======================================================
            // TAHAP 1: HITUNG B_PIEZO_METRIK
            // ======================================================
            
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

            // 3. HITUNG SEMUA LOKASI MENGGUNAKAN MODEL B_PIEZO_METRIK
            $hasilBmetrik = $this->metrikModel->hitungSemuaLokasi(
                $pengukuran_id,
                $dataPembacaan,
                $dataIreading
            );

            // 4. SIMPAN HASIL B_PIEZO_METRIK KE DATABASE
            $simpanBmetrik = $this->metrikModel->simpanHasilPerhitungan($pengukuran_id, $hasilBmetrik);
            if (!$simpanBmetrik) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Gagal menyimpan hasil perhitungan B_piezo_metrik ke database"
                ]);
            }

            log_message('debug', "[Hitungright] Tahap 1 (B_piezo_metrik) selesai untuk pengukuran_id: $pengukuran_id");

            // ======================================================
            // TAHAP 2: HITUNG PERHITUNGAN_T_PSMETRIK (FINAL)
            // ======================================================
            
            // 5. AMBIL DATA ELV_PIEZ DARI i_reading_atas
            $dataElvPiez = $this->getDataElvPiez($pengukuran_id);
            if (empty($dataElvPiez)) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data Elv_Piez tidak ditemukan untuk pengukuran_id: $pengukuran_id"
                ]);
            }

            // 6. HITUNG RUMUS FINAL: Elv_Piez - Hasil_B_Metrik
            $hasilFinal = $this->perhitunganPsmetrikModel->hitungSemuaLokasi(
                $pengukuran_id,
                $dataElvPiez,
                $hasilBmetrik
            );

            // 7. SIMPAN HASIL FINAL KE PERHITUNGAN_T_PSMETRIK
            $simpanFinal = $this->perhitunganPsmetrikModel->simpanHasilPerhitungan($pengukuran_id, $hasilFinal);
            if (!$simpanFinal) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Gagal menyimpan hasil perhitungan final ke database"
                ]);
            }

            log_message('debug', "[Hitungright] Tahap 2 (Perhitungan_t_psmetrik) selesai untuk pengukuran_id: $pengukuran_id");

            // 8. FORMAT RESPONSE
            $response = $this->formatResponseDuaTahap($pengukuran_id, $hasilBmetrik, $hasilFinal);

            log_message('debug', "[Hitungright] Perhitungan 2 tahap selesai untuk pengukuran_id: $pengukuran_id");

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
     * ENDPOINT - HITUNG SATU LOKASI SPESIFIK (2 TAHAP)
     * =========================================================================
     * 
     * Menghitung satu lokasi tertentu (R-01, R-02, ..., PZ-04) dalam 2 tahap
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

            log_message('debug', "[Hitungright] Memulai perhitungan 2 tahap untuk $lokasi, pengukuran_id: $pengukuran_id");

            // ======================================================
            // TAHAP 1: HITUNG B_PIEZO_METRIK
            // ======================================================
            
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

            // 4. HITUNG DAN SIMPAN B_PIEZO_METRIK
            $resultBmetrik = $this->metrikModel->hitungDanSimpanLokasi(
                $pengukuran_id,
                $lokasi,
                $pembacaan['feet'],
                $pembacaan['inch'],
                $ireading['kedalaman']
            );

            log_message('debug', "[Hitungright] Tahap 1 (B_piezo_metrik) $lokasi selesai. Hasil: " . $resultBmetrik['hasil']);

            // ======================================================
            // TAHAP 2: HITUNG PERHITUNGAN_T_PSMETRIK (FINAL)
            // ======================================================
            
            // 5. AMBIL DATA ELV_PIEZ UNTUK LOKASI INI
            $elvPiez = $this->ireadingAtasModel
                ->where('id_pengukuran', $pengukuran_id)
                ->where('titik_piezometer', $lokasi)
                ->first();

            if (!$elvPiez || empty($elvPiez['Elv_Piez'])) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data Elv_Piez untuk $lokasi tidak ditemukan!"
                ]);
            }

            // 6. HITUNG DAN SIMPAN PERHITUNGAN FINAL
            $resultFinal = $this->perhitunganPsmetrikModel->hitungDanSimpanLokasi(
                $pengukuran_id,
                $lokasi,
                $elvPiez['Elv_Piez'],
                $resultBmetrik['hasil']
            );

            log_message('debug', "[Hitungright] Tahap 2 (Perhitungan_t_psmetrik) $lokasi selesai. Hasil Final: " . $resultFinal['hasil_final']);

            // 7. GABUNGKAN HASIL DARI KEDUA TAHAP
            $combinedResult = [
                'tahap_1_b_metrik' => $resultBmetrik,
                'tahap_2_final' => $resultFinal,
                'ringkasan' => [
                    'lokasi' => $lokasi,
                    'elv_piez' => $elvPiez['Elv_Piez'],
                    'hasil_b_metrik' => $resultBmetrik['hasil'],
                    'hasil_final' => $resultFinal['hasil_final'],
                    'rumus_final' => 'Elv_Piez - Hasil_B_Metrik'
                ]
            ];

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Perhitungan 2 tahap $lokasi berhasil",
                "data" => $combinedResult
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
     * ENDPOINT - PROSES PERHITUNGAN LENGKAP OTOMATIS
     * =========================================================================
     * 
     * Menggunakan method prosesPerhitunganLengkap dari model Perhitungan_t_psmetrik
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function prosesLengkap()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id diperlukan!"
                ]);
            }

            log_message('debug', "[Hitungright] Memulai proses perhitungan lengkap untuk pengukuran_id: $pengukuran_id");

            // Panggil method prosesPerhitunganLengkap dari model
            $hasil = $this->perhitunganPsmetrikModel->prosesPerhitunganLengkap($pengukuran_id);

            if ($hasil === false) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Gagal melakukan proses perhitungan lengkap"
                ]);
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Proses perhitungan lengkap berhasil",
                "data" => [
                    'pengukuran_id' => $pengukuran_id,
                    'total_lokasi' => count($this->lokasiList),
                    'hasil_perhitungan' => $hasil
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', '[Hitungright] Error prosesLengkap: ' . $e->getMessage());
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

            // Panggil endpoint hitungLokasi (2 tahap)
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
            $totalSudahDihitungBmetrik = 0;
            $totalSudahDihitungFinal = 0;

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

                // Cek hasil perhitungan B_metrik
                $metrik = $this->metrikModel->find($pengukuran_id);
                $hasilBmetrik = $metrik[$lokasi] ?? null;
                $sudahDihitungBmetrik = !empty($hasilBmetrik);

                // Cek hasil perhitungan final
                $final = $this->perhitunganPsmetrikModel->find($pengukuran_id);
                $hasilFinal = $final[$lokasi] ?? null;
                $sudahDihitungFinal = !empty($hasilFinal);

                $status[$lokasi] = [
                    'pembacaan_ada' => !empty($pembacaan),
                    'ireading_ada' => !empty($ireading),
                    'sudah_dihitung_bmetrik' => $sudahDihitungBmetrik,
                    'sudah_dihitung_final' => $sudahDihitungFinal,
                    'hasil_bmetrik' => $hasilBmetrik,
                    'hasil_final' => $hasilFinal,
                    'feet_input' => $pembacaan['feet'] ?? null,
                    'inch_input' => $pembacaan['inch'] ?? null,
                    'kedalaman' => $ireading['kedalaman'] ?? null,
                    'elv_piez' => $ireading['Elv_Piez'] ?? null
                ];

                if (!empty($pembacaan)) $totalDataPembacaan++;
                if (!empty($ireading)) $totalDataIreading++;
                if ($sudahDihitungBmetrik) $totalSudahDihitungBmetrik++;
                if ($sudahDihitungFinal) $totalSudahDihitungFinal++;
            }

            return $this->response->setJSON([
                "status" => "success",
                "data" => [
                    'pengukuran_id' => $pengukuran_id,
                    'total_lokasi' => count($this->lokasiList),
                    'statistik' => [
                        'data_pembacaan' => $totalDataPembacaan,
                        'data_ireading' => $totalDataIreading,
                        'sudah_dihitung_bmetrik' => $totalSudahDihitungBmetrik,
                        'sudah_dihitung_final' => $totalSudahDihitungFinal,
                        'belum_dihitung_final' => count($this->lokasiList) - $totalSudahDihitungFinal
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
     * ENDPOINT - GET HASIL PERHITUNGAN B_METRIK
     * =========================================================================
     * 
     * Mengambil hasil perhitungan B_piezo_metrik yang sudah disimpan
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function getHasilBmetrik()
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
                    "message" => "Data hasil perhitungan B_piezo_metrik tidak ditemukan!"
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
            log_message('error', '[Hitungright] Error getHasilBmetrik: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan: " . $e->getMessage()
            ]);
        }
    }

    /**
     * =========================================================================
     * ENDPOINT - GET HASIL PERHITUNGAN FINAL
     * =========================================================================
     * 
     * Mengambil hasil perhitungan final dari Perhitungan_t_psmetrik
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function getHasilFinal()
    {
        try {
            $pengukuran_id = $this->request->getGet('pengukuran_id');

            if (!$pengukuran_id) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter pengukuran_id diperlukan!"
                ]);
            }

            $final = $this->perhitunganPsmetrikModel->find($pengukuran_id);

            if (!$final) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Data hasil perhitungan final tidak ditemukan!"
                ]);
            }

            // Format hasil
            $hasil = [];
            foreach ($this->lokasiList as $lokasi) {
                $hasil[$lokasi] = $final[$lokasi] ?? null;
            }

            return $this->response->setJSON([
                "status" => "success",
                "data" => [
                    'pengukuran_id' => $pengukuran_id,
                    'hasil_perhitungan_final' => $hasil,
                    'keterangan' => 'Hasil dari rumus: Elv_Piez - Hasil_B_Metrik'
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', '[Hitungright] Error getHasilFinal: ' . $e->getMessage());
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Terjadi kesalahan: " . $e->getMessage()
            ]);
        }
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
            $elv_piez = $this->request->getGet('elv_piez');

            if ($feet === null || $inch === null || $kedalaman === null) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Parameter feet, inch, dan kedalaman diperlukan!"
                ]);
            }

            // Test rumus B_metrik
            $hasilBmetrik = $this->metrikModel->hitungRumusPiezo($feet, $inch, $kedalaman);

            // Test rumus final jika elv_piez diberikan
            $hasilFinal = null;
            if ($elv_piez !== null) {
                $hasilFinal = $this->perhitunganPsmetrikModel->hitungRumusFinal($elv_piez, $hasilBmetrik);
            }

            $response = [
                "status" => "success",
                "data" => [
                    'feet_input' => $feet,
                    'inch_input' => $inch,
                    'kedalaman' => $kedalaman,
                    'elv_piez' => $elv_piez,
                    'hasil_bmetrik' => $hasilBmetrik,
                    'rumus_bmetrik' => $this->metrikModel->getRumusTerpakai($feet),
                    'keterangan_bmetrik' => $feet === 'KERING' ? 
                        'Menggunakan kedalaman langsung (kondisi KERING)' : 
                        'Menggunakan rumus: (feet × 0.3048) + (inch × 0.0254)'
                ]
            ];

            if ($elv_piez !== null) {
                $response['data']['hasil_final'] = $hasilFinal;
                $response['data']['rumus_final'] = 'Elv_Piez - Hasil_B_Metrik';
                $response['data']['keterangan_final'] = "Perhitungan final: $elv_piez - $hasilBmetrik = $hasilFinal";
            }

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            log_message('error', '[Hitungright] Error testRumus: ' . $e->getMessage());
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
     * Ambil data Elv_Piez dari tabel i_reading_atas
     */
    private function getDataElvPiez($pengukuran_id)
    {
        $ireadingData = $this->ireadingAtasModel
            ->where('id_pengukuran', $pengukuran_id)
            ->findAll();

        $formattedData = [];
        foreach ($ireadingData as $data) {
            $formattedData[$data['titik_piezometer']] = $data['Elv_Piez'];
        }

        return $formattedData;
    }

    /**
     * Format response untuk perhitungan 2 tahap
     */
    private function formatResponseDuaTahap($pengukuran_id, $hasilBmetrik, $hasilFinal)
    {
        $detail = [];

        foreach ($this->lokasiList as $lokasi) {
            $bMetrik = $hasilBmetrik[$lokasi] ?? ['hasil' => null];
            $final = $hasilFinal[$lokasi] ?? ['hasil' => null];
            
            $detail[$lokasi] = [
                'tahap_1_b_metrik' => [
                    'hasil' => $bMetrik['hasil'],
                    'feet_input' => $bMetrik['feet_input'] ?? null,
                    'inch_input' => $bMetrik['inch_input'] ?? null,
                    'kedalaman' => $bMetrik['kedalaman'] ?? null,
                    'rumus_terpakai' => $bMetrik['rumus_terpakai'] ?? null
                ],
                'tahap_2_final' => [
                    'hasil' => $final['hasil'],
                    'elv_piez' => $final['elv_piez'] ?? null,
                    'hasil_b_metrik' => $final['hasil_b_metrik'] ?? null,
                    'rumus' => $final['rumus'] ?? 'Elv_Piez - Hasil_B_Metrik'
                ]
            ];
        }

        return [
            "status" => "success",
            "message" => "Perhitungan 2 tahap selesai. " . count($this->lokasiList) . " lokasi berhasil diproses.",
            "data" => [
                'pengukuran_id' => $pengukuran_id,
                'total_lokasi' => count($this->lokasiList),
                'tahap_1' => 'B_piezo_metrik: IF(feet="KERING", kedalaman, (feet×0.3048)+(inch×0.0254))',
                'tahap_2' => 'Perhitungan_t_psmetrik: Elv_Piez - Hasil_B_Metrik',
                'detail' => $detail
            ]
        ];
    }
}