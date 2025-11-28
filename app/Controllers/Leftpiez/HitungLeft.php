<?php
namespace App\Controllers\LeftPiez;

use App\Controllers\BaseController;
use App\Models\LeftPiez\MetrikModel;
use App\Models\LeftPiez\PerhitunganLeftPiezModel;
use App\Models\LeftPiez\TPembacaanLeftPiezModel;

class HitungLeft extends BaseController
{
    protected $metrikModel;
    protected $perhitunganModel;
    protected $pembacaanModel;

    protected $validPiezometers = [
        'L01', 'L02', 'L03', 'L04', 'L05', 
        'L06', 'L07', 'L08', 'L09', 'L10', 'SPZ02'
    ];

    public function __construct()
    {
        $this->metrikModel = new MetrikModel();
        $this->perhitunganModel = new PerhitunganLeftPiezModel();
        $this->pembacaanModel = new TPembacaanLeftPiezModel();
        
        log_message('info', '[HitungLeft] Controller initialized dengan model terpadu');
    }

 /**
 * Hitung untuk lokasi tertentu saja (dari pembacaan feet/inch)
 */
public function hitungLokasi($idPengukuran, $lokasi)
{
    log_message('info', "[HitungLeft] hitungLokasi: idPengukuran={$idPengukuran}, lokasi={$lokasi}");
    
    try {
        $piezometerKey = $this->normalizePiezometerKey($lokasi);
        
        log_message('debug', "[HitungLeft] Lokasi dinormalisasi: {$lokasi} -> {$piezometerKey}");
        
        if (!$this->pembacaanModel->isValidPiezometer($piezometerKey)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Piezometer tidak valid: ' . $lokasi
            ]);
        }

        // Ambil kedalaman
        $perhitungan = $this->perhitunganModel
            ->where('id_pengukuran', $idPengukuran)
            ->where('tipe_piezometer', $piezometerKey)
            ->first();
        
        $kedalaman = $perhitungan['kedalaman'] ?? $this->perhitunganModel->getDefaultKedalaman($piezometerKey);

        // Ambil pembacaan
        $pembacaan = $this->pembacaanModel->getByPengukuranDanTipe($idPengukuran, $piezometerKey);

        if (!$pembacaan) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data pembacaan tidak ditemukan'
            ]);
        }

        // Hitung nilai metrik
        $nilaiMetrik = $this->metrikModel->hitungL(
            $pembacaan['feet'], 
            $pembacaan['inch'], 
            $kedalaman
        );

        log_message('debug', "[HitungLeft] Nilai metrik dihitung: {$nilaiMetrik}");

        // ✅ PERBAIKAN: Mapping nama kolom yang benar
        $kolomMapping = [
            'L01' => 'l_01',
            'L02' => 'l_02', 
            'L03' => 'l_03',
            'L04' => 'l_04',
            'L05' => 'l_05',
            'L06' => 'l_06',
            'L07' => 'l_07',
            'L08' => 'l_08',
            'L09' => 'l_09',
            'L10' => 'l_10',
            'SPZ02' => 'spz_02'  // ✅ INI YANG BENAR!
        ];

        $kolomMetrik = $kolomMapping[$piezometerKey] ?? null;
        
        if (!$kolomMetrik) {
            log_message('error', "[HitungLeft] Kolom tidak ditemukan untuk: {$piezometerKey}");
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kolom metrik tidak ditemukan untuk: ' . $piezometerKey
            ]);
        }

        log_message('debug', "[HitungLeft] Kolom metrik: {$kolomMetrik}");

        // Update atau insert ke tabel metrik
        $existingMetrik = $this->metrikModel->where('id_pengukuran', $idPengukuran)->first();
        
        log_message('debug', "[HitungLeft] Existing metrik: " . ($existingMetrik ? 'ADA' : 'TIDAK ADA'));

        if ($existingMetrik) {
            $updateData = [
                $kolomMetrik => $nilaiMetrik,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            log_message('debug', "[HitungLeft] Update data: " . json_encode($updateData));
            
            $result = $this->metrikModel->update($existingMetrik['id_bacaan_metrik'], $updateData);
            log_message('debug', "[HitungLeft] Update result: " . ($result ? 'BERHASIL' : 'GAGAL') . ", ID: " . $existingMetrik['id_bacaan_metrik']);
        } else {
            $insertData = [
                'id_pengukuran' => $idPengukuran,
                $kolomMetrik => $nilaiMetrik,
                'M_feet' => 0.3048,
                'M_inch' => 0.0254,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            log_message('debug', "[HitungLeft] Insert data: " . json_encode($insertData));
            
            $result = $this->metrikModel->insert($insertData);
            $insertId = $this->metrikModel->getInsertID();
            log_message('debug', "[HitungLeft] Insert result: " . ($result ? 'BERHASIL' : 'GAGAL') . ", ID: " . $insertId);
        }

        // Hitung nilai rumus
        $nilaiRumus = $this->perhitunganModel->hitungNilai($idPengukuran, $piezometerKey);

        // Simpan ke tabel perhitungan
        $existingPerhitungan = $this->perhitunganModel
            ->where('id_pengukuran', $idPengukuran)
            ->where('tipe_piezometer', $piezometerKey)
            ->first();
        
        if ($existingPerhitungan) {
            $this->perhitunganModel->update($existingPerhitungan['id_perhitungan'], [
                't_psmetrik' => $nilaiRumus
            ]);
        } else {
            $this->perhitunganModel->insert([
                'id_pengukuran' => $idPengukuran,
                'tipe_piezometer' => $piezometerKey,
                't_psmetrik' => $nilaiRumus
            ]);
        }

        log_message('info', "[HitungLeft] Perhitungan berhasil untuk {$lokasi}");

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Perhitungan berhasil untuk ' . $lokasi,
            'data' => [
                'metrik' => [
                    'feet' => $pembacaan['feet'],
                    'inch' => $pembacaan['inch'],
                    'kedalaman' => $kedalaman,
                    'hasil_metrik' => $nilaiMetrik
                ],
                'rumus' => [
                    'piezometer' => $piezometerKey,
                    'hasil_rumus' => $nilaiRumus
                ]
            ]
        ]);

    } catch (\Exception $e) {
        log_message('error', '[HitungLeft] Error hitungLokasi: ' . $e->getMessage());
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Gagal menghitung: ' . $e->getMessage()
        ]);
    }
}

    /**
     * Hitung semua lokasi dari pembacaan
     */
    public function hitungSemuaDariPembacaan($idPengukuran)
    {
        log_message('info', "[HitungLeft] hitungSemuaDariPembacaan: idPengukuran={$idPengukuran}");
        
        try {
            $dataUpdate = [
                'id_pengukuran' => $idPengukuran,
                'M_feet' => 0.3048,
                'M_inch' => 0.0254,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $hasData = false;
            $lokasiDihitung = [];
            $lokasiTidakDihitung = [];

            // Ambil semua data pembacaan sekaligus
            $semuaPembacaan = $this->pembacaanModel->getByPengukuran($idPengukuran);
            $pembacaanByTipe = [];
            
            foreach ($semuaPembacaan as $pembacaan) {
                $pembacaanByTipe[$pembacaan['tipe_piezometer']] = $pembacaan;
            }

            foreach ($this->validPiezometers as $piezometer) {
                if (isset($pembacaanByTipe[$piezometer])) {
                    $pembacaan = $pembacaanByTipe[$piezometer];
                    
                    $perhitungan = $this->perhitunganModel
                        ->where('id_pengukuran', $idPengukuran)
                        ->where('tipe_piezometer', $piezometer)
                        ->first();
                    
                    $kedalaman = $perhitungan['kedalaman'] ?? $this->perhitunganModel->getDefaultKedalaman($piezometer);

                    $nilai = $this->metrikModel->hitungL(
                        $pembacaan['feet'], 
                        $pembacaan['inch'], 
                        $kedalaman
                    );
                    
                    $kolomMetrik = 'l_' . strtolower(substr($piezometer, 1));
                    $dataUpdate[$kolomMetrik] = $nilai;
                    $hasData = true;
                    $lokasiDihitung[] = $piezometer;
                } else {
                    $kolomMetrik = 'l_' . strtolower(substr($piezometer, 1));
                    $dataUpdate[$kolomMetrik] = null;
                    $lokasiTidakDihitung[] = $piezometer;
                }
            }

            if (!$hasData) {
                return $this->response->setJSON([
                    'status' => 'info',
                    'message' => 'Tidak ada data pembacaan untuk dihitung'
                ]);
            }

            $existing = $this->metrikModel->where('id_pengukuran', $idPengukuran)->first();
            
            if ($existing) {
                $this->metrikModel->update($existing['id_bacaan_metrik'], $dataUpdate);
            } else {
                $dataUpdate['created_at'] = date('Y-m-d H:i:s');
                $this->metrikModel->insert($dataUpdate);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Semua perhitungan dari pembacaan berhasil',
                'id_pengukuran' => $idPengukuran,
                'lokasi_dihitung' => $lokasiDihitung,
                'lokasi_tidak_dihitung' => $lokasiTidakDihitung
            ]);

        } catch (\Exception $e) {
            log_message('error', '[HitungLeft] Error hitungSemuaDariPembacaan: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghitung: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Hitung nilai untuk semua piezometer sekaligus (rumus Elv_Piez - l_XX)
     */
    public function hitungSemuaRumus($id_pengukuran)
    {
        log_message('info', "[HitungLeft] hitungSemuaRumus: id_pengukuran={$id_pengukuran}");
        
        $results = [];
        $rumusDihitung = [];
        $rumusGagal = [];
        
        foreach ($this->validPiezometers as $piezometer) {
            try {
                $result = $this->perhitunganModel->hitungNilai($id_pengukuran, $piezometer);
                $results[$piezometer] = $result;
                $rumusDihitung[] = $piezometer;
            } catch (\Exception $e) {
                $results[$piezometer] = null;
                $rumusGagal[] = $piezometer;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'id_pengukuran' => $id_pengukuran,
            'results' => $results,
            'ringkasan' => [
                'total_rumus' => count($this->validPiezometers),
                'berhasil_dihitung' => count($rumusDihitung),
                'gagal_dihitung' => count($rumusGagal)
            ]
        ]);
    }

    /**
     * Hitung nilai untuk piezometer tertentu (rumus Elv_Piez - l_XX)
     */
    public function hitungRumus($piezometer, $id_pengukuran)
    {
        log_message('info', "[HitungLeft] hitungRumus: piezometer={$piezometer}, id_pengukuran={$id_pengukuran}");

        $piezometer = strtoupper($piezometer);
        
        if (!in_array($piezometer, $this->validPiezometers)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Piezometer tidak valid. Pilihan: ' . implode(', ', $this->validPiezometers)
            ]);
        }

        try {
            $result = $this->perhitunganModel->hitungNilai($id_pengukuran, $piezometer);
            
            return $this->response->setJSON([
                'success' => true,
                'piezometer' => $piezometer,
                'id_pengukuran' => $id_pengukuran,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            log_message('error', "[HitungLeft] hitungRumus {$piezometer} gagal: " . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error menghitung rumus: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Simpan data perhitungan untuk semua piezometer
     */
    public function simpanSemuaRumus()
    {
        $data = $this->request->getJSON(true);
        $id_pengukuran = $data['id_pengukuran'] ?? null;
        
        if (!$id_pengukuran) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID pengukuran diperlukan'
            ]);
        }

        $results = [];
        $errors = [];

        foreach ($this->validPiezometers as $piezometer) {
            try {
                $nilai = $this->perhitunganModel->hitungNilai($id_pengukuran, $piezometer);
                
                $existing = $this->perhitunganModel
                    ->where('id_pengukuran', $id_pengukuran)
                    ->where('tipe_piezometer', $piezometer)
                    ->first();
                
                if ($existing) {
                    $this->perhitunganModel->update($existing['id_perhitungan'], [
                        't_psmetrik' => $nilai
                    ]);
                } else {
                    $this->perhitunganModel->insert([
                        'id_pengukuran' => $id_pengukuran,
                        'tipe_piezometer' => $piezometer,
                        't_psmetrik' => $nilai
                    ]);
                }
                $results[$piezometer] = $nilai;
            } catch (\Exception $e) {
                $errors[$piezometer] = $e->getMessage();
            }
        }

        $success = empty($errors);

        return $this->response->setJSON([
            'success' => $success,
            'id_pengukuran' => $id_pengukuran,
            'results' => $results,
            'errors' => $errors
        ]);
    }

    /**
     * Simpan data perhitungan untuk piezometer tertentu
     */
    public function simpanRumus($piezometer)
    {
        log_message('info', "[HitungLeft] simpanRumus: piezometer={$piezometer}");

        $piezometer = strtoupper($piezometer);
        
        if (!in_array($piezometer, $this->validPiezometers)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Piezometer tidak valid'
            ]);
        }

        $data = $this->request->getJSON(true);
        $id_pengukuran = $data['id_pengukuran'] ?? null;
        
        if (!$id_pengukuran) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID pengukuran diperlukan'
            ]);
        }

        try {
            $nilai = $this->perhitunganModel->hitungNilai($id_pengukuran, $piezometer);
            
            $existing = $this->perhitunganModel
                ->where('id_pengukuran', $id_pengukuran)
                ->where('tipe_piezometer', $piezometer)
                ->first();
            
            $updateData = ['t_psmetrik' => $nilai];
            
            // Tambahkan field tambahan jika ada
            $optionalFields = ['elv_piez', 'kedalaman', 'koordinat_x', 'koordinat_y', 'record_max', 'record_min'];
            foreach ($optionalFields as $field) {
                if (isset($data[$field])) $updateData[$field] = $data[$field];
            }
            
            if ($existing) {
                $this->perhitunganModel->update($existing['id_perhitungan'], $updateData);
            } else {
                $updateData['id_pengukuran'] = $id_pengukuran;
                $updateData['tipe_piezometer'] = $piezometer;
                $this->perhitunganModel->insert($updateData);
            }

            return $this->response->setJSON([
                'success' => true,
                'piezometer' => $piezometer,
                'id_pengukuran' => $id_pengukuran,
                'result' => $nilai
            ]);

        } catch (\Exception $e) {
            log_message('error', "[HitungLeft] simpanRumus {$piezometer} gagal: " . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ambil data perhitungan yang sudah disimpan
     */
    public function getDataRumus($piezometer = null)
    {
        $id_pengukuran = $this->request->getGet('id_pengukuran');
        
        if (!$id_pengukuran) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID pengukuran diperlukan'
            ]);
        }

        if ($piezometer) {
            $piezometer = strtoupper($piezometer);
            
            if (!in_array($piezometer, $this->validPiezometers)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Piezometer tidak valid'
                ]);
            }

            $data = $this->perhitunganModel
                ->where('id_pengukuran', $id_pengukuran)
                ->where('tipe_piezometer', $piezometer)
                ->first();
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } else {
            $results = [];
            
            foreach ($this->validPiezometers as $piezometer) {
                $data = $this->perhitunganModel
                    ->where('id_pengukuran', $id_pengukuran)
                    ->where('tipe_piezometer', $piezometer)
                    ->first();
                
                $results[$piezometer] = $data;
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $results
            ]);
        }
    }

    /**
     * Update data perhitungan
     */
    public function updateDataRumus($piezometer)
    {
        log_message('info', "[HitungLeft] updateDataRumus: piezometer={$piezometer}");

        $piezometer = strtoupper($piezometer);
        
        if (!in_array($piezometer, $this->validPiezometers)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Piezometer tidak valid'
            ]);
        }

        $data = $this->request->getJSON(true);
        $id_pengukuran = $data['id_pengukuran'] ?? null;
        
        if (!$id_pengukuran) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID pengukuran diperlukan'
            ]);
        }

        $existing = $this->perhitunganModel
            ->where('id_pengukuran', $id_pengukuran)
            ->where('tipe_piezometer', $piezometer)
            ->first();
        
        if (!$existing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        try {
            $this->perhitunganModel->update($existing['id_perhitungan'], $data);
            
            $nilai = $this->perhitunganModel->hitungNilai($id_pengukuran, $piezometer);
            
            $this->perhitunganModel->update($existing['id_perhitungan'], [
                't_psmetrik' => $nilai
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Data berhasil diupdate',
                'result' => $nilai
            ]);

        } catch (\Exception $e) {
            log_message('error', "[HitungLeft] updateDataRumus {$piezometer} gagal: " . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Dashboard - tampilkan status semua perhitungan
     */
    public function dashboard($id_pengukuran)
    {
        log_message('info', "[HitungLeft] dashboard: id_pengukuran={$id_pengukuran}");

        $statusPembacaan = [];
        $statusRumus = [];

        // Cek status pembacaan
        foreach ($this->validPiezometers as $piezometer) {
            $pembacaan = $this->pembacaanModel->getByPengukuranDanTipe($id_pengukuran, $piezometer);
            $statusPembacaan[$piezometer] = $pembacaan ? true : false;
        }

        // Cek status rumus
        foreach ($this->validPiezometers as $piezometer) {
            $data = $this->perhitunganModel
                ->where('id_pengukuran', $id_pengukuran)
                ->where('tipe_piezometer', $piezometer)
                ->first();
            $statusRumus[$piezometer] = $data ? true : false;
        }

        $totalPembacaan = count(array_filter($statusPembacaan));
        $totalRumus = count(array_filter($statusRumus));

        return $this->response->setJSON([
            'success' => true,
            'id_pengukuran' => $id_pengukuran,
            'status_pembacaan' => $statusPembacaan,
            'status_rumus' => $statusRumus,
            'ringkasan' => [
                'total_pembacaan' => count($statusPembacaan),
                'pembacaan_ada' => $totalPembacaan,
                'total_rumus' => count($statusRumus),
                'rumus_ada' => $totalRumus
            ]
        ]);
    }

    /**
     * Get semua tipe piezometer yang valid
     */
    public function getPiezometers()
    {
        return $this->response->setJSON([
            'success' => true,
            'piezometers' => $this->validPiezometers,
            'total' => count($this->validPiezometers)
        ]);
    }

    /**
     * Normalisasi key piezometer
     */
    private function normalizePiezometerKey($lokasi)
    {
        $lokasi = strtoupper($lokasi);
        
        if ($lokasi === 'SPZ02') {
            return 'SPZ02';
        }
        
        if (preg_match('/^L[\_ ]?(\d+)$/i', $lokasi, $matches)) {
            return 'L' . str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        }
        
        return $lokasi;
    }
}