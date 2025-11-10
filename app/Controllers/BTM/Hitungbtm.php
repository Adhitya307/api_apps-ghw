<?php
namespace App\Controllers\BTM;

use App\Controllers\BaseController;
use App\Models\Btm\PengukuranBtmModel;
use App\Models\Btm\BacaanBt1Model;
use App\Models\Btm\BacaanBt2Model;
use App\Models\Btm\BacaanBt3Model;
use App\Models\Btm\BacaanBt4Model;
use App\Models\Btm\BacaanBt5Model;
use App\Models\Btm\BacaanBt6Model;
use App\Models\Btm\BacaanBt7Model;
use App\Models\Btm\BacaanBt8Model;
use App\Models\Btm\PerhitunganBt1Model;
use App\Models\Btm\PerhitunganBt2Model;
use App\Models\Btm\PerhitunganBt3Model;
use App\Models\Btm\PerhitunganBt4Model;
use App\Models\Btm\PerhitunganBt5Model;
use App\Models\Btm\PerhitunganBt6Model;
use App\Models\Btm\PerhitunganBt7Model;
use App\Models\Btm\PerhitunganBt8Model;

class Hitungbtm extends BaseController
{
    protected $pengukuranModel;
    protected $bacaanModels;
    protected $perhitunganModels;

    public function __construct()
    {
        $this->pengukuranModel = new PengukuranBtmModel();
        
        // Inisialisasi model bacaan untuk semua BT
        $this->bacaanModels = [
            'bt1' => new BacaanBt1Model(),
            'bt2' => new BacaanBt2Model(),
            'bt3' => new BacaanBt3Model(),
            'bt4' => new BacaanBt4Model(),
            'bt5' => new BacaanBt5Model(),
            'bt6' => new BacaanBt6Model(),
            'bt7' => new BacaanBt7Model(),
            'bt8' => new BacaanBt8Model()
        ];
        
        // Inisialisasi model perhitungan untuk semua BT
        $this->perhitunganModels = [
            'bt1' => new PerhitunganBt1Model(),
            'bt2' => new PerhitunganBt2Model(),
            'bt3' => new PerhitunganBt3Model(),
            'bt4' => new PerhitunganBt4Model(),
            'bt5' => new PerhitunganBt5Model(),
            'bt6' => new PerhitunganBt6Model(),
            'bt7' => new PerhitunganBt7Model(),
            'bt8' => new PerhitunganBt8Model()
        ];
    }

    /**
     * Hitung perhitungan untuk pengukuran tertentu dengan data sebelumnya
     * SAMA PERSIS dengan method di BtmController
     */
    public function calculateForPengukuran($id_pengukuran_sekarang)
    {
        try {
            // Ambil data pengukuran saat ini
            $pengukuranSekarang = $this->pengukuranModel->find($id_pengukuran_sekarang);
            
            if (!$pengukuranSekarang) {
                return [
                    'success' => false,
                    'message' => 'Data pengukuran tidak ditemukan'
                ];
            }

            // Ambil data pengukuran sebelumnya (berdasarkan tanggal)
            $pengukuranSebelumnya = $this->pengukuranModel
                ->where('tanggal <', $pengukuranSekarang['tanggal'])
                ->orderBy('tanggal', 'DESC')
                ->first();

            $results = [];

            // Hitung untuk setiap BT (1-8)
            foreach ($this->perhitunganModels as $key => $perhitunganModel) {
                $bacaanModel = $this->bacaanModels[$key];
                
                // Ambil data bacaan sebelumnya jika ada
                $bacaanSebelumnya = null;
                if ($pengukuranSebelumnya) {
                    $bacaanSebelumnya = $bacaanModel->getByPengukuran($pengukuranSebelumnya['id_pengukuran']);
                }

                // Hitung rumus dengan data sebelumnya
                $methodName = 'hitungRumus' . ucfirst($key);
                if (method_exists($perhitunganModel, $methodName)) {
                    $result = $perhitunganModel->$methodName(
                        $id_pengukuran_sekarang, 
                        $bacaanModel, 
                        $bacaanSebelumnya
                    );

                    // Simpan ke database
                    $existing = $perhitunganModel->getByPengukuran($id_pengukuran_sekarang);
                    if ($existing) {
                        $perhitunganModel->update($existing['id_perhitungan'], $result);
                    } else {
                        $perhitunganModel->insert($result);
                    }

                    $results[$key] = $result;
                }
            }

            return [
                'success' => true,
                'message' => 'Perhitungan berhasil untuk semua BT',
                'data' => $results
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error in calculateForPengukuran: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Hitung perhitungan untuk BT tertentu saja
     */
    public function calculateForBt($id_pengukuran_sekarang, $bt_key)
    {
        try {
            if (!isset($this->perhitunganModels[$bt_key]) || !isset($this->bacaanModels[$bt_key])) {
                return [
                    'success' => false,
                    'message' => 'Model untuk ' . $bt_key . ' tidak ditemukan'
                ];
            }

            // Ambil data pengukuran saat ini
            $pengukuranSekarang = $this->pengukuranModel->find($id_pengukuran_sekarang);
            
            if (!$pengukuranSekarang) {
                return [
                    'success' => false,
                    'message' => 'Data pengukuran tidak ditemukan'
                ];
            }

            // Ambil data pengukuran sebelumnya (berdasarkan tanggal)
            $pengukuranSebelumnya = $this->pengukuranModel
                ->where('tanggal <', $pengukuranSekarang['tanggal'])
                ->orderBy('tanggal', 'DESC')
                ->first();

            $perhitunganModel = $this->perhitunganModels[$bt_key];
            $bacaanModel = $this->bacaanModels[$bt_key];
            
            // Ambil data bacaan sebelumnya jika ada
            $bacaanSebelumnya = null;
            if ($pengukuranSebelumnya) {
                $bacaanSebelumnya = $bacaanModel->getByPengukuran($pengukuranSebelumnya['id_pengukuran']);
            }

            // Hitung rumus dengan data sebelumnya
            $methodName = 'hitungRumus' . ucfirst($bt_key);
            if (method_exists($perhitunganModel, $methodName)) {
                $result = $perhitunganModel->$methodName(
                    $id_pengukuran_sekarang, 
                    $bacaanModel, 
                    $bacaanSebelumnya
                );

                // Simpan ke database
                $existing = $perhitunganModel->getByPengukuran($id_pengukuran_sekarang);
                if ($existing) {
                    $perhitunganModel->update($existing['id_perhitungan'], $result);
                } else {
                    $perhitunganModel->insert($result);
                }

                return [
                    'success' => true,
                    'message' => 'Perhitungan berhasil untuk ' . $bt_key,
                    'data' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Method ' . $methodName . ' tidak ditemukan'
                ];
            }

        } catch (\Exception $e) {
            log_message('error', 'Error in calculateForBt: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * API Endpoint untuk hitung semua BT
     */
    public function hitungSemua()
    {
        try {
            $pengukuran_id = $this->request->getPost('pengukuran_id') ?? $this->request->getGet('pengukuran_id');
            
            if (empty($pengukuran_id)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parameter pengukuran_id harus diisi'
                ]);
            }

            $result = $this->calculateForPengukuran($pengukuran_id);

            return $this->response->setJSON($result);

        } catch (\Exception $e) {
            log_message('error', 'Error in hitungSemua: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API Endpoint untuk hitung BT tertentu - FIXED FOR ANDROID
     */
    public function hitungBt()
    {
        try {
            $pengukuran_id = $this->request->getPost('pengukuran_id') ?? $this->request->getGet('pengukuran_id');
            $bt_number = $this->request->getPost('bt_number') ?? $this->request->getGet('bt_number');
            
            // Jika bt_number tidak ada, coba bt_key (backward compatibility)
            if (empty($bt_number)) {
                $bt_key = $this->request->getPost('bt_key') ?? $this->request->getGet('bt_key');
                // Extract number from bt_key (e.g., "bt1" -> 1)
                if (!empty($bt_key) && preg_match('/bt(\d+)/', $bt_key, $matches)) {
                    $bt_number = $matches[1];
                }
            }
            
            if (empty($pengukuran_id) || empty($bt_number)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parameter pengukuran_id dan bt_number harus diisi'
                ]);
            }

            // Konversi bt_number ke bt_key
            $bt_key = "bt" . $bt_number;
            
            // Validasi bt_key
            $valid_bt_keys = ['bt1', 'bt2', 'bt3', 'bt4', 'bt5', 'bt6', 'bt7', 'bt8'];
            if (!in_array($bt_key, $valid_bt_keys)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'bt_number harus antara 1 sampai 8'
                ]);
            }

            $result = $this->calculateForBt($pengukuran_id, $bt_key);

            return $this->response->setJSON($result);

        } catch (\Exception $e) {
            log_message('error', 'Error in hitungBt: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API Endpoint untuk hitung BT tertentu + SCATTER - COMPLETE FOR ANDROID
     */
    public function hitungBubbleTilt()
    {
        try {
            // Ambil data dari JSON body
            $json = $this->request->getJSON();
            
            if ($json) {
                $pengukuran_id = $json->pengukuran_id ?? null;
                $bt_number = $json->bt_number ?? null;
            } else {
                // Fallback ke POST data
                $pengukuran_id = $this->request->getPost('pengukuran_id');
                $bt_number = $this->request->getPost('bt_number');
            }
            
            log_message('debug', 'Hitung BubbleTilt - pengukuran_id: ' . $pengukuran_id . ', bt_number: ' . $bt_number);
            
            if (empty($pengukuran_id) || empty($bt_number)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parameter pengukuran_id dan bt_number harus diisi'
                ]);
            }

            // Konversi bt_number ke bt_key
            $bt_key = "bt" . $bt_number;
            
            // Validasi bt_key
            $valid_bt_keys = ['bt1', 'bt2', 'bt3', 'bt4', 'bt5', 'bt6', 'bt7', 'bt8'];
            if (!in_array($bt_key, $valid_bt_keys)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'bt_number harus antara 1 sampai 8'
                ]);
            }

            // 1. HITUNG PERHITUNGAN BT (YANG SUDAH BERJALAN)
            $result_perhitungan = $this->calculateForBt($pengukuran_id, $bt_key);
            
            if (!$result_perhitungan['success']) {
                return $this->response->setJSON($result_perhitungan);
            }

            // 2. HITUNG SCATTER DATA (TAMBAHAN BARU)
            $result_scatter = $this->calculateScatterForBt($pengukuran_id, $bt_key);
            
            // Format response yang lengkap untuk Android
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Perhitungan BT dan Scatter berhasil untuk ' . $bt_key,
                'data' => [
                    'perhitungan' => $result_perhitungan['data'],
                    'scatter' => $result_scatter['data'] ?? null,
                    'scatter_success' => $result_scatter['success'],
                    'scatter_message' => $result_scatter['message'] ?? 'Scatter calculated'
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in hitungBubbleTilt: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Hitung scatter data untuk BT tertentu
     */
    public function calculateScatterForBt($id_pengukuran, $bt_key)
    {
        try {
            // Load scatter models
            $scatterModels = [
                'bt1' => new \App\Models\Btm\ScatterBt1Model(),
                'bt2' => new \App\Models\Btm\ScatterBt2Model(),
                'bt3' => new \App\Models\Btm\ScatterBt3Model(),
                'bt4' => new \App\Models\Btm\ScatterBt4Model(),
                'bt5' => new \App\Models\Btm\ScatterBt5Model(),
                'bt6' => new \App\Models\Btm\ScatterBt6Model(),
                'bt7' => new \App\Models\Btm\ScatterBt7Model(),
                'bt8' => new \App\Models\Btm\ScatterBt8Model()
            ];

            if (!isset($scatterModels[$bt_key])) {
                return [
                    'success' => false,
                    'message' => 'Scatter model untuk ' . $bt_key . ' tidak ditemukan'
                ];
            }

            $scatterModel = $scatterModels[$bt_key];
            
            // Ambil data bacaan
            $bacaanModel = $this->bacaanModels[$bt_key];
            $bacaanData = $bacaanModel->getByPengukuran($id_pengukuran);
            
            if (!$bacaanData) {
                return [
                    'success' => false,
                    'message' => 'Data bacaan tidak ditemukan untuk perhitungan scatter'
                ];
            }

            // Ambil data perhitungan untuk A_sec dan B_sec
            $perhitunganModel = $this->perhitunganModels[$bt_key];
            $perhitunganData = $perhitunganModel->getByPengukuran($id_pengukuran);
            
            if (!$perhitunganData) {
                return [
                    'success' => false,
                    'message' => 'Data perhitungan tidak ditemukan untuk scatter'
                ];
            }

            // Hitung scatter menggunakan method yang sama seperti di BtmController
            $A_sec = $perhitunganData['A_sec'] ?? 0;
            $B_sec = $perhitunganData['B_sec'] ?? 0;
            $usArah = $bacaanData['US_Arah'] ?? 'U';
            $tbArah = $bacaanData['TB_Arah'] ?? 'T';

            // Rumus scatter: Y_US = IF(US_Arah="U";A_sec;(-A_sec))
            $Y_US = ($usArah == 'U') ? $A_sec : (-$A_sec);
            
            // Rumus scatter: X_TB = IF(TB_Arah="T";B_sec;(-B_sec))
            $X_TB = ($tbArah == 'T') ? $B_sec : (-$B_sec);
            
            // Ambil scatter data sebelumnya untuk kumulatif
            $previousScatter = $this->getPreviousScatterData($scatterModel, $id_pengukuran);
            
            // Hitung kumulatif
            $previous_Y_cum = $previousScatter ? (float)$previousScatter['Y_cum'] : 0;
            $previous_X_cum = $previousScatter ? (float)$previousScatter['X_cum'] : 0;
            
            $Y_cum = $previous_Y_cum + $Y_US;
            $X_cum = $previous_X_cum + $X_TB;
            
            $scatterData = [
                'id_pengukuran' => $id_pengukuran,
                'Y_US' => $Y_US,
                'X_TB' => $X_TB,
                'Y_cum' => $Y_cum,
                'X_cum' => $X_cum
            ];
            
            // Simpan scatter data
            $existingScatter = $scatterModel->where('id_pengukuran', $id_pengukuran)->first();
            if ($existingScatter) {
                $scatterModel->update($existingScatter['id_scatter'], $scatterData);
            } else {
                $scatterModel->insert($scatterData);
            }

            return [
                'success' => true,
                'message' => 'Scatter data berhasil dihitung',
                'data' => $scatterData
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error in calculateScatterForBt: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error scatter: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Ambil scatter data sebelumnya untuk kumulatif
     */
    private function getPreviousScatterData($scatterModel, $id_pengukuran)
    {
        try {
            $tableName = $scatterModel->table;
            
            return $scatterModel->select("$tableName.*")
                ->join('t_pengukuran_btm t', "t.id_pengukuran = $tableName.id_pengukuran")
                ->where("$tableName.id_pengukuran <", $id_pengukuran)
                ->orderBy('t.tanggal', 'DESC')
                ->orderBy('t.tahun', 'DESC')
                ->orderBy('t.periode', 'DESC')
                ->first();
        } catch (\Exception $e) {
            log_message('error', 'Error in getPreviousScatterData: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Hitung ulang semua data perhitungan
     */
    public function recalculateAll()
    {
        try {
            // Ambil semua data pengukuran diurutkan
            $allPengukuran = $this->pengukuranModel
                ->orderBy('tanggal', 'ASC')
                ->orderBy('tahun', 'ASC')
                ->orderBy('periode', 'ASC')
                ->findAll();
            
            $results = [];
            
            foreach ($allPengukuran as $pengukuran) {
                $result = $this->calculateForPengukuran($pengukuran['id_pengukuran']);
                $results[] = [
                    'id_pengukuran' => $pengukuran['id_pengukuran'],
                    'result' => $result
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Semua data berhasil dihitung ulang',
                'total_data' => count($allPengukuran),
                'results' => $results
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in recalculateAll: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Debug method untuk melihat data perhitungan
     */
    public function debugPerhitungan($id_pengukuran = null)
    {
        if (!$id_pengukuran) {
            $allPengukuran = $this->pengukuranModel->findAll();
            $id_pengukuran = $allPengukuran[0]['id_pengukuran'] ?? null;
        }
        
        $debugData = [];
        
        foreach ($this->perhitunganModels as $btKey => $perhitunganModel) {
            $currentData = $perhitunganModel->getByPengukuran($id_pengukuran);
            $bacaanData = $this->bacaanModels[$btKey]->getByPengukuran($id_pengukuran);
            
            $debugData[$btKey] = [
                'bacaan' => $bacaanData,
                'perhitungan' => $currentData
            ];
        }
        
        return $this->response->setJSON([
            'success' => true,
            'id_pengukuran' => $id_pengukuran,
            'debug_data' => $debugData
        ]);
    }

    /**
     * Health check endpoint
     */
    public function healthCheck()
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Hitungbtm Controller is working',
            'timestamp' => date('Y-m-d H:i:s'),
            'available_methods' => [
                'calculateForPengukuran',
                'calculateForBt', 
                'hitungSemua',
                'hitungBt',
                'hitungBubbleTilt',
                'calculateScatterForBt',
                'recalculateAll',
                'debugPerhitungan'
            ]
        ]);
    }
}