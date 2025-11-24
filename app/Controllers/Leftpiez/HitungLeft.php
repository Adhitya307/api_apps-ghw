<?php
namespace App\Controllers\LeftPiez;

use App\Controllers\BaseController;
use App\Models\LeftPiez\MetrikModel;

// Perhitungan
use App\Models\LeftPiez\PerhitunganL01Model;
use App\Models\LeftPiez\PerhitunganL02Model;
use App\Models\LeftPiez\PerhitunganL03Model;
use App\Models\LeftPiez\PerhitunganL04Model;
use App\Models\LeftPiez\PerhitunganL05Model;
use App\Models\LeftPiez\PerhitunganL06Model;
use App\Models\LeftPiez\PerhitunganL07Model;
use App\Models\LeftPiez\PerhitunganL08Model;
use App\Models\LeftPiez\PerhitunganL09Model;
use App\Models\LeftPiez\PerhitunganL10Model;
use App\Models\LeftPiez\PerhitunganSPZ02Model;

// Pembacaan
use App\Models\LeftPiez\TPembacaanL01Model;
use App\Models\LeftPiez\TPembacaanL02Model;
use App\Models\LeftPiez\TPembacaanL03Model;
use App\Models\LeftPiez\TPembacaanL04Model;
use App\Models\LeftPiez\TPembacaanL05Model;
use App\Models\LeftPiez\TPembacaanL06Model;
use App\Models\LeftPiez\TPembacaanL07Model;
use App\Models\LeftPiez\TPembacaanL08Model;
use App\Models\LeftPiez\TPembacaanL09Model;
use App\Models\LeftPiez\TPembacaanL10Model;
use App\Models\LeftPiez\TPembacaanSPZ02Model;

class HitungLeft extends BaseController
{
    protected $metrikModel;

    protected $mapping = [
        'l_01' => ['perhitungan' => PerhitunganL01Model::class, 'pembacaan' => TPembacaanL01Model::class],
        'l_02' => ['perhitungan' => PerhitunganL02Model::class, 'pembacaan' => TPembacaanL02Model::class],
        'l_03' => ['perhitungan' => PerhitunganL03Model::class, 'pembacaan' => TPembacaanL03Model::class],
        'l_04' => ['perhitungan' => PerhitunganL04Model::class, 'pembacaan' => TPembacaanL04Model::class],
        'l_05' => ['perhitungan' => PerhitunganL05Model::class, 'pembacaan' => TPembacaanL05Model::class],
        'l_06' => ['perhitungan' => PerhitunganL06Model::class, 'pembacaan' => TPembacaanL06Model::class],
        'l_07' => ['perhitungan' => PerhitunganL07Model::class, 'pembacaan' => TPembacaanL07Model::class],
        'l_08' => ['perhitungan' => PerhitunganL08Model::class, 'pembacaan' => TPembacaanL08Model::class],
        'l_09' => ['perhitungan' => PerhitunganL09Model::class, 'pembacaan' => TPembacaanL09Model::class],
        'l_10' => ['perhitungan' => PerhitunganL10Model::class, 'pembacaan' => TPembacaanL10Model::class],
        'spz_02' => ['perhitungan' => PerhitunganSPZ02Model::class, 'pembacaan' => TPembacaanSPZ02Model::class],
    ];

    // Models untuk perhitungan rumus Elv_Piez - l_XX
    protected $perhitunganModels = [];

    public function __construct()
    {
        $this->metrikModel = new MetrikModel();
        
        // Inisialisasi models untuk perhitungan rumus
        $this->perhitunganModels = [
            'L01' => new PerhitunganL01Model(),
            'L02' => new PerhitunganL02Model(),
            'L03' => new PerhitunganL03Model(),
            'L04' => new PerhitunganL04Model(),
            'L05' => new PerhitunganL05Model(),
            'L06' => new PerhitunganL06Model(),
            'L07' => new PerhitunganL07Model(),
            'L08' => new PerhitunganL08Model(),
            'L09' => new PerhitunganL09Model(),
            'L10' => new PerhitunganL10Model(),
            'SPZ02' => new PerhitunganSPZ02Model()
        ];

        log_message('info', '[HitungLeft] Controller initialized dengan ' . count($this->perhitunganModels) . ' model perhitungan');
    }

    /**
     ===========================================================================
     BAGIAN 1: PERHITUNGAN DARI PEMBACAAN (feet, inch) ke nilai metrik
     ===========================================================================
     */

    /**
     * Hitung untuk lokasi tertentu saja (dari pembacaan feet/inch)
     */
/**
 * Hitung untuk lokasi tertentu saja (dari pembacaan feet/inch)
 */
public function hitungLokasi($idPengukuran, $lokasi)
{
    log_message('info', "[HitungLeft] hitungLokasi dipanggil: idPengukuran={$idPengukuran}, lokasi={$lokasi}");
    
    try {
        // Normalisasi nama lokasi (contoh: "L01" menjadi "l_01")
        $kolom = $this->normalizeLokasi($lokasi);
        $piezometerKey = strtoupper(str_replace('_', '', $kolom)); // "l_01" -> "L01"
        
        log_message('debug', "[HitungLeft] Lokasi dinormalisasi: {$lokasi} -> {$kolom} -> {$piezometerKey}");
        
        if (!array_key_exists($kolom, $this->mapping)) {
            log_message('warning', "[HitungLeft] Lokasi tidak valid: {$lokasi}");
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Lokasi tidak valid: ' . $lokasi
            ]);
        }

        $models = $this->mapping[$kolom];
        $pembacaanModel = new $models['pembacaan']();
        $perhitunganModel = new $models['perhitungan']();

        // Ambil kedalaman default
        $perhitungan = $perhitunganModel->where('id_pengukuran', $idPengukuran)->first();
        $kedalaman = $perhitungan['kedalaman'] ?? 71.15;

        log_message('debug', "[HitungLeft] Kedalaman yang digunakan: {$kedalaman}");

        // Ambil pembacaan terbaru
        $pembacaan = $pembacaanModel->where('id_pengukuran', $idPengukuran)->first();

        if (!$pembacaan) {
            log_message('warning', "[HitungLeft] Data pembacaan tidak ditemukan untuk id_pengukuran={$idPengukuran}");
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data pembacaan tidak ditemukan'
            ]);
        }

        log_message('debug', "[HitungLeft] Data pembacaan ditemukan: feet={$pembacaan['feet']}, inch={$pembacaan['inch']}");

        // Hitung nilai metrik
        $nilaiMetrik = $this->metrikModel->hitungL(
            $pembacaan['feet'], 
            $pembacaan['inch'], 
            $kedalaman
        );

        log_message('info', "[HitungLeft] Perhitungan metrik selesai: feet={$pembacaan['feet']}, inch={$pembacaan['inch']} -> nilai_metrik={$nilaiMetrik}");

        // Update atau insert ke tabel metrik
        $existingMetrik = $this->metrikModel->where('id_pengukuran', $idPengukuran)->first();
        
        if ($existingMetrik) {
            // Update existing record - GUNAKAN id_bacaan_metrik sebagai primary key
            log_message('debug', "[HitungLeft] Update data metrik existing id_bacaan_metrik={$existingMetrik['id_bacaan_metrik']}");
            $this->metrikModel->update($existingMetrik['id_bacaan_metrik'], [
                $kolom => $nilaiMetrik,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            // Insert new record
            log_message('debug', "[HitungLeft] Insert data metrik baru");
            $dataInsert = [
                'id_pengukuran' => $idPengukuran,
                $kolom => $nilaiMetrik,
                'M_feet' => 0.3048, // Default value
                'M_inch' => 0.0254, // Default value
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->metrikModel->insert($dataInsert);
        }

        // 🚨 BAGIAN BARU: HITUNG RUMUS OTOMATIS SETELAH METRIK
        log_message('info', "[HitungLeft] Memulai perhitungan rumus untuk {$piezometerKey}");
        
        // Cek apakah model perhitungan tersedia
        if (!array_key_exists($piezometerKey, $this->perhitunganModels)) {
            log_message('warning', "[HitungLeft] Model perhitungan tidak ditemukan untuk: {$piezometerKey}");
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Model perhitungan tidak ditemukan untuk: ' . $lokasi
            ]);
        }

        $modelPerhitungan = $this->perhitunganModels[$piezometerKey];
        $methodName = 'hitung' . $piezometerKey; // contoh: hitungL01, hitungL02, dll

        // Pastikan method perhitungan ada
        if (!method_exists($modelPerhitungan, $methodName)) {
            log_message('error', "[HitungLeft] Method perhitungan tidak ditemukan: {$methodName}");
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Method perhitungan tidak ditemukan: ' . $methodName
            ]);
        }

        // Hitung nilai rumus (Elv_Piez - l_XX)
        $nilaiRumus = $modelPerhitungan->$methodName($idPengukuran);
        log_message('info', "[HitungLeft] Perhitungan rumus {$piezometerKey} selesai: {$nilaiRumus}");

        // Simpan ke tabel perhitungan
        $existingPerhitungan = $modelPerhitungan->where('id_pengukuran', $idPengukuran)->first();
        
        if ($existingPerhitungan) {
            // Update data perhitungan yang sudah ada
            log_message('debug', "[HitungLeft] Update data perhitungan existing id_perhitungan={$existingPerhitungan['id_perhitungan']}");
            
            $updateData = [
                
                't_psmetrik_' . $piezometerKey => $nilaiRumus
            ];
            
            $modelPerhitungan->update($existingPerhitungan['id_perhitungan'], $updateData);
            log_message('debug', "[HitungLeft] Data perhitungan {$piezometerKey} diupdate");
        } else {
            // Insert data perhitungan baru
            log_message('debug', "[HitungLeft] Insert data perhitungan baru untuk {$piezometerKey}");
            
            $insertData = [
                'id_pengukuran' => $idPengukuran,
                
                't_psmetrik_' . $piezometerKey => $nilaiRumus
            ];
            
            $modelPerhitungan->insert($insertData);
            log_message('debug', "[HitungLeft] Data perhitungan {$piezometerKey} diinsert");
        }

        log_message('info', "[HitungLeft] Semua perhitungan berhasil untuk {$lokasi}");

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
     * Hitung semua lokasi dari pembacaan (opsional)
     */
    public function hitungSemuaDariPembacaan($idPengukuran)
    {
        log_message('info', "[HitungLeft] hitungSemuaDariPembacaan dipanggil: idPengukuran={$idPengukuran}");
        
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

            foreach ($this->mapping as $kolom => $models) {
                $pembacaanModel = new $models['pembacaan']();
                $perhitunganModel = new $models['perhitungan']();

                $perhitungan = $perhitunganModel->where('id_pengukuran', $idPengukuran)->first();
                $kedalaman = $perhitungan['kedalaman'] ?? 71.15;

                $pembacaan = $pembacaanModel->where('id_pengukuran', $idPengukuran)->first();

                if ($pembacaan) {
                    $nilai = $this->metrikModel->hitungL(
                        $pembacaan['feet'], 
                        $pembacaan['inch'], 
                        $kedalaman
                    );
                    $dataUpdate[$kolom] = $nilai;
                    $hasData = true;
                    $lokasiDihitung[] = $kolom;
                    
                    log_message('debug', "[HitungLeft] {$kolom}: feet={$pembacaan['feet']}, inch={$pembacaan['inch']} -> nilai={$nilai}");
                } else {
                    $dataUpdate[$kolom] = null;
                    $lokasiTidakDihitung[] = $kolom;
                    log_message('debug', "[HitungLeft] {$kolom}: Tidak ada data pembacaan");
                }
            }

            log_message('info', "[HitungLeft] Ringkasan perhitungan: " . count($lokasiDihitung) . " dihitung, " . count($lokasiTidakDihitung) . " tidak dihitung");

            if (!$hasData) {
                log_message('warning', "[HitungLeft] Tidak ada data pembacaan untuk dihitung");
                return $this->response->setJSON([
                    'status' => 'info',
                    'message' => 'Tidak ada data pembacaan untuk dihitung'
                ]);
            }

            $existing = $this->metrikModel->where('id_pengukuran', $idPengukuran)->first();
            
            if ($existing) {
                // Gunakan id_bacaan_metrik sebagai primary key
                log_message('debug', "[HitungLeft] Update data metrik existing id_bacaan_metrik={$existing['id_bacaan_metrik']}");
                $this->metrikModel->update($existing['id_bacaan_metrik'], $dataUpdate);
            } else {
                log_message('debug', "[HitungLeft] Insert data metrik baru");
                $dataUpdate['created_at'] = date('Y-m-d H:i:s');
                $this->metrikModel->insert($dataUpdate);
            }

            log_message('info', "[HitungLeft] Semua perhitungan dari pembacaan berhasil");

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
     ===========================================================================
     BAGIAN 2: PERHITUNGAN RUMUS Elv_Piez - l_XX (seperti di model-model awal)
     ===========================================================================
     */

    /**
     * Hitung nilai untuk semua piezometer sekaligus (rumus Elv_Piez - l_XX)
     */
    public function hitungSemuaRumus($id_pengukuran)
    {
        log_message('info', "[HitungLeft] hitungSemuaRumus dipanggil: id_pengukuran={$id_pengukuran}");
        
        $results = [];
        $rumusDihitung = [];
        $rumusGagal = [];
        
        foreach ($this->perhitunganModels as $key => $model) {
            $methodName = 'hitung' . $key;
            if (method_exists($model, $methodName)) {
                try {
                    log_message('debug', "[HitungLeft] Menghitung rumus {$key} dengan method {$methodName}");
                    $result = $model->$methodName($id_pengukuran);
                    $results[$key] = $result;
                    $rumusDihitung[] = $key;
                    log_message('debug', "[HitungLeft] Rumus {$key} berhasil: {$result}");
                } catch (\Exception $e) {
                    log_message('error', "[HitungLeft] Rumus {$key} gagal: " . $e->getMessage());
                    $results[$key] = null;
                    $rumusGagal[] = $key;
                }
            } else {
                log_message('warning', "[HitungLeft] Method {$methodName} tidak ditemukan untuk model {$key}");
                $results[$key] = null;
                $rumusGagal[] = $key;
            }
        }

        log_message('info', "[HitungLeft] Ringkasan hitungSemuaRumus: " . count($rumusDihitung) . " berhasil, " . count($rumusGagal) . " gagal");

        return $this->response->setJSON([
            'success' => true,
            'id_pengukuran' => $id_pengukuran,
            'results' => $results,
            'ringkasan' => [
                'total_rumus' => count($this->perhitunganModels),
                'berhasil_dihitung' => count($rumusDihitung),
                'gagal_dihitung' => count($rumusGagal),
                'rumus_dihitung' => $rumusDihitung,
                'rumus_gagal' => $rumusGagal
            ]
        ]);
    }

    /**
     * Hitung nilai untuk piezometer tertentu (rumus Elv_Piez - l_XX)
     */
    public function hitungRumus($piezometer, $id_pengukuran)
    {
        log_message('info', "[HitungLeft] hitungRumus dipanggil: piezometer={$piezometer}, id_pengukuran={$id_pengukuran}");

        if (!array_key_exists($piezometer, $this->perhitunganModels)) {
            log_message('warning', "[HitungLeft] Piezometer tidak valid: {$piezometer}");
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Piezometer tidak valid. Pilihan: ' . implode(', ', array_keys($this->perhitunganModels))
            ])->setStatusCode(400);
        }

        $model = $this->perhitunganModels[$piezometer];
        $methodName = 'hitung' . $piezometer;
        
        log_message('debug', "[HitungLeft] Menggunakan model: " . get_class($model) . ", method: {$methodName}");

        if (!method_exists($model, $methodName)) {
            log_message('error', "[HitungLeft] Method perhitungan tidak ditemukan: {$methodName}");
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Method perhitungan tidak ditemukan'
            ])->setStatusCode(400);
        }

        try {
            $result = $model->$methodName($id_pengukuran);
            log_message('info', "[HitungLeft] Perhitungan rumus {$piezometer} berhasil: {$result}");
            
            return $this->response->setJSON([
                'success' => true,
                'piezometer' => $piezometer,
                'id_pengukuran' => $id_pengukuran,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            log_message('error', "[HitungLeft] Perhitungan rumus {$piezometer} gagal: " . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error menghitung rumus: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Simpan data perhitungan untuk semua piezometer (rumus Elv_Piez - l_XX)
     */
    public function simpanSemuaRumus()
    {
        $data = $this->request->getJSON(true);
        $id_pengukuran = $data['id_pengukuran'] ?? null;
        
        log_message('info', "[HitungLeft] simpanSemuaRumus dipanggil: id_pengukuran={$id_pengukuran}");
        
        if (!$id_pengukuran) {
            log_message('warning', "[HitungLeft] ID pengukuran tidak diberikan");
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID pengukuran diperlukan'
            ])->setStatusCode(400);
        }

        $results = [];
        $errors = [];
        $rumusDisimpan = [];
        $rumusGagal = [];

        foreach ($this->perhitunganModels as $key => $model) {
            try {
                log_message('debug', "[HitungLeft] Memproses piezometer: {$key}");
                
                // Cek apakah data sudah ada
                $existing = $model->where('id_pengukuran', $id_pengukuran)->first();
                
                if ($existing) {
                    log_message('debug', "[HitungLeft] Data existing ditemukan untuk {$key}, id_perhitungan={$existing['id_perhitungan']}");
                    
                    // Update data yang sudah ada
                    $methodName = 'hitung' . $key;
                    $nilai = $model->$methodName($id_pengukuran);
                    
                    $updateData = [
                        
                        't_psmetrik_' . $key => $nilai
                    ];
                    
                    $model->update($existing['id_perhitungan'], $updateData);
                    $results[$key] = $nilai;
                    $rumusDisimpan[] = $key;
                    
                    log_message('debug', "[HitungLeft] {$key} berhasil diupdate: {$nilai}");
                } else {
                    log_message('debug', "[HitungLeft] Data baru untuk {$key}");
                    
                    // Insert data baru
                    $insertData = ['id_pengukuran' => $id_pengukuran];
                    $model->insert($insertData);
                    
                    $methodName = 'hitung' . $key;
                    $results[$key] = $model->$methodName($id_pengukuran);
                    $rumusDisimpan[] = $key;
                    
                    log_message('debug', "[HitungLeft] {$key} berhasil diinsert: {$results[$key]}");
                }
            } catch (\Exception $e) {
                $errorMsg = $e->getMessage();
                $errors[$key] = $errorMsg;
                $rumusGagal[] = $key;
                log_message('error', "[HitungLeft] {$key} gagal: {$errorMsg}");
            }
        }

        $success = empty($errors);
        $logLevel = $success ? 'info' : 'warning';
        
        log_message($logLevel, "[HitungLeft] Ringkasan simpanSemuaRumus: " . 
            count($rumusDisimpan) . " berhasil, " . count($rumusGagal) . " gagal");

        return $this->response->setJSON([
            'success' => $success,
            'id_pengukuran' => $id_pengukuran,
            'results' => $results,
            'errors' => $errors,
            'ringkasan' => [
                'total_rumus' => count($this->perhitunganModels),
                'berhasil_disimpan' => count($rumusDisimpan),
                'gagal_disimpan' => count($rumusGagal),
                'rumus_disimpan' => $rumusDisimpan,
                'rumus_gagal' => $rumusGagal
            ]
        ]);
    }

    /**
     * Simpan data perhitungan untuk piezometer tertentu (rumus Elv_Piez - l_XX)
     */
    public function simpanRumus($piezometer)
    {
        log_message('info', "[HitungLeft] simpanRumus dipanggil: piezometer={$piezometer}");

        if (!array_key_exists($piezometer, $this->perhitunganModels)) {
            log_message('warning', "[HitungLeft] Piezometer tidak valid: {$piezometer}");
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Piezometer tidak valid. Pilihan: ' . implode(', ', array_keys($this->perhitunganModels))
            ])->setStatusCode(400);
        }

        $data = $this->request->getJSON(true);
        $id_pengukuran = $data['id_pengukuran'] ?? null;
        
        log_message('debug', "[HitungLeft] Data request: id_pengukuran={$id_pengukuran}");
        
        if (!$id_pengukuran) {
            log_message('warning', "[HitungLeft] ID pengukuran tidak diberikan");
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID pengukuran diperlukan'
            ])->setStatusCode(400);
        }

        $model = $this->perhitunganModels[$piezometer];
        
        try {
            // Cek apakah data sudah ada
            $existing = $model->where('id_pengukuran', $id_pengukuran)->first();
            
            if ($existing) {
                log_message('debug', "[HitungLeft] Data existing ditemukan, id_perhitungan={$existing['id_perhitungan']}");
                
                // Update data yang sudah ada
                $methodName = 'hitung' . $piezometer;
                $nilai = $model->$methodName($id_pengukuran);
                
                $updateData = [
                    
                    't_psmetrik_' . $piezometer => $nilai
                ];
                
                // Tambahkan field Elv_Piez dan kedalaman jika ada dalam request
                if (isset($data['Elv_Piez'])) $updateData['Elv_Piez'] = $data['Elv_Piez'];
                if (isset($data['kedalaman'])) $updateData['kedalaman'] = $data['kedalaman'];
                if (isset($data['koordinat_x'])) $updateData['koordinat_x'] = $data['koordinat_x'];
                if (isset($data['koordinat_y'])) $updateData['koordinat_y'] = $data['koordinat_y'];
                
                log_message('debug', "[HitungLeft] Update data: " . json_encode($updateData));
                $model->update($existing['id_perhitungan'], $updateData);
                $result = $nilai;
                
                log_message('info', "[HitungLeft] {$piezometer} berhasil diupdate: {$nilai}");
            } else {
                log_message('debug', "[HitungLeft] Data baru untuk {$piezometer}");
                
                // Insert data baru
                $insertData = ['id_pengukuran' => $id_pengukuran];
                
                // Tambahkan field tambahan jika ada
                if (isset($data['Elv_Piez'])) $insertData['Elv_Piez'] = $data['Elv_Piez'];
                if (isset($data['kedalaman'])) $insertData['kedalaman'] = $data['kedalaman'];
                if (isset($data['koordinat_x'])) $insertData['koordinat_x'] = $data['koordinat_x'];
                if (isset($data['koordinat_y'])) $insertData['koordinat_y'] = $data['koordinat_y'];
                
                log_message('debug', "[HitungLeft] Insert data: " . json_encode($insertData));
                $model->insert($insertData);
                
                $methodName = 'hitung' . $piezometer;
                $result = $model->$methodName($id_pengukuran);
                
                log_message('info', "[HitungLeft] {$piezometer} berhasil diinsert: {$result}");
            }

            return $this->response->setJSON([
                'success' => true,
                'piezometer' => $piezometer,
                'id_pengukuran' => $id_pengukuran,
                'result' => $result
            ]);

        } catch (\Exception $e) {
            log_message('error', "[HitungLeft] simpanRumus {$piezometer} gagal: " . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Ambil data perhitungan yang sudah disimpan
     */
    public function getDataRumus($piezometer = null)
    {
        $id_pengukuran = $this->request->getGet('id_pengukuran');
        
        log_message('info', "[HitungLeft] getDataRumus dipanggil: piezometer={$piezometer}, id_pengukuran={$id_pengukuran}");
        
        if (!$id_pengukuran) {
            log_message('warning', "[HitungLeft] ID pengukuran tidak diberikan");
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID pengukuran diperlukan'
            ])->setStatusCode(400);
        }

        if ($piezometer) {
            // Ambil data untuk piezometer tertentu
            if (!array_key_exists($piezometer, $this->perhitunganModels)) {
                log_message('warning', "[HitungLeft] Piezometer tidak valid: {$piezometer}");
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Piezometer tidak valid'
                ])->setStatusCode(400);
            }

            $data = $this->perhitunganModels[$piezometer]->where('id_pengukuran', $id_pengukuran)->first();
            $status = $data ? 'ditemukan' : 'tidak ditemukan';
            
            log_message('debug', "[HitungLeft] Data {$piezometer}: {$status}");
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } else {
            // Ambil data untuk semua piezometer
            $results = [];
            $dataDitemukan = [];
            $dataTidakDitemukan = [];
            
            foreach ($this->perhitunganModels as $key => $model) {
                $data = $model->where('id_pengukuran', $id_pengukuran)->first();
                $results[$key] = $data;
                
                if ($data) {
                    $dataDitemukan[] = $key;
                } else {
                    $dataTidakDitemukan[] = $key;
                }
            }

            log_message('info', "[HitungLeft] Ringkasan getDataRumus: " . 
                count($dataDitemukan) . " ditemukan, " . count($dataTidakDitemukan) . " tidak ditemukan");
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $results,
                'ringkasan' => [
                    'data_ditemukan' => $dataDitemukan,
                    'data_tidak_ditemukan' => $dataTidakDitemukan
                ]
            ]);
        }
    }

    /**
     * Update data perhitungan (Elv_Piez, kedalaman, dll)
     */
    public function updateDataRumus($piezometer)
    {
        log_message('info', "[HitungLeft] updateDataRumus dipanggil: piezometer={$piezometer}");

        if (!array_key_exists($piezometer, $this->perhitunganModels)) {
            log_message('warning', "[HitungLeft] Piezometer tidak valid: {$piezometer}");
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Piezometer tidak valid'
            ])->setStatusCode(400);
        }

        $data = $this->request->getJSON(true);
        $id_pengukuran = $data['id_pengukuran'] ?? null;
        
        log_message('debug', "[HitungLeft] Data request: " . json_encode($data));
        
        if (!$id_pengukuran) {
            log_message('warning', "[HitungLeft] ID pengukuran tidak diberikan");
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID pengukuran diperlukan'
            ])->setStatusCode(400);
        }

        $model = $this->perhitunganModels[$piezometer];
        $existing = $model->where('id_pengukuran', $id_pengukuran)->first();
        
        if (!$existing) {
            log_message('warning', "[HitungLeft] Data tidak ditemukan untuk piezometer={$piezometer}, id_pengukuran={$id_pengukuran}");
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ])->setStatusCode(404);
        }

        try {
            // Update data
            log_message('debug', "[HitungLeft] Update data existing id_perhitungan={$existing['id_perhitungan']}");
            $model->update($existing['id_perhitungan'], $data);
            
            // Hitung ulang nilai
            $methodName = 'hitung' . $piezometer;
            $nilai = $model->$methodName($id_pengukuran);
            
            // Update nilai yang dihitung
            $model->update($existing['id_perhitungan'], [
                
                't_psmetrik_' . $piezometer => $nilai
            ]);

            log_message('info', "[HitungLeft] {$piezometer} berhasil diupdate dan dihitung ulang: {$nilai}");

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
            ])->setStatusCode(500);
        }
    }

    /**
     ===========================================================================
     FUNGSI UTILITAS
     ===========================================================================
     */

    /**
     * Normalisasi nama lokasi
     */
    private function normalizeLokasi($lokasi)
    {
        $lokasi = strtolower($lokasi);
        
        if ($lokasi === 'spz02') {
            return 'spz_02';
        }
        
        // Format: "l01" menjadi "l_01"
        if (preg_match('/^l(\d+)$/', $lokasi, $matches)) {
            return 'l_' . str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        }
        
        return $lokasi;
    }

    /**
     * Dashboard - tampilkan status semua perhitungan
     */
    public function dashboard($id_pengukuran)
    {
        log_message('info', "[HitungLeft] dashboard dipanggil: id_pengukuran={$id_pengukuran}");

        $statusPembacaan = [];
        $statusRumus = [];

        // Cek status perhitungan dari pembacaan
        foreach ($this->mapping as $kolom => $models) {
            $pembacaanModel = new $models['pembacaan']();
            $pembacaan = $pembacaanModel->where('id_pengukuran', $id_pengukuran)->first();
            $statusPembacaan[$kolom] = $pembacaan ? true : false;
        }

        // Cek status perhitungan rumus
        foreach ($this->perhitunganModels as $key => $model) {
            $data = $model->where('id_pengukuran', $id_pengukuran)->first();
            $statusRumus[$key] = $data ? true : false;
        }

        $totalPembacaan = count(array_filter($statusPembacaan));
        $totalRumus = count(array_filter($statusRumus));

        log_message('info', "[HitungLeft] Status dashboard: {$totalPembacaan}/" . count($statusPembacaan) . " pembacaan, {$totalRumus}/" . count($statusRumus) . " rumus");

        return $this->response->setJSON([
            'success' => true,
            'id_pengukuran' => $id_pengukuran,
            'status_pembacaan' => $statusPembacaan,
            'status_rumus' => $statusRumus,
            'ringkasan' => [
                'total_pembacaan' => count($statusPembacaan),
                'pembacaan_ada' => $totalPembacaan,
                'pembacaan_tidak_ada' => count($statusPembacaan) - $totalPembacaan,
                'total_rumus' => count($statusRumus),
                'rumus_ada' => $totalRumus,
                'rumus_tidak_ada' => count($statusRumus) - $totalRumus
            ]
        ]);
    }

    /**
     * Log semua method yang tersedia untuk debugging
     */
    public function debugMethods()
    {
        log_message('info', "[HitungLeft] debugMethods dipanggil");
        
        $methods = [];
        foreach ($this->perhitunganModels as $key => $model) {
            $methodName = 'hitung' . $key;
            $methods[$key] = [
                'model' => get_class($model),
                'method' => $methodName,
                'exists' => method_exists($model, $methodName)
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'methods' => $methods,
            'total_models' => count($this->perhitunganModels)
        ]);
    }
}