<?php

namespace App\Controllers\Rightpiezo;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Rightpiezo\T_pengukuran_rightpiez;
use App\Models\Rightpiezo\I_reading_atas;
use App\Models\Rightpiezo\T_pembacaan;
use App\Models\Rightpiezo\B_piezo_metrik;
use App\Models\Rightpiezo\Perhitungan_t_psmetrik;

class RightPiezoApiController extends BaseController
{
    use ResponseTrait;

    // ==================== MASTER DATA - PENGUKURAN ====================
    
    /**
     * Get all pengukuran data
     * GET /rightpiezo/pengukuran
     */
    public function pengukuran()
    {
        try {
            $model = new T_pengukuran_rightpiez();
            $data = $model->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data pengukuran right piezometer berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data pengukuran: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get pengukuran by ID
     * GET /rightpiezo/pengukuran/{id}
     */
    public function pengukuranById($id = null)
    {
        try {
            $model = new T_pengukuran_rightpiez();
            $data = $model->find($id);

            if (!$data) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Data pengukuran dengan ID ' . $id . ' tidak ditemukan',
                    'data' => null
                ], 404);
            }

            return $this->respond([
                'success' => true,
                'message' => 'Data pengukuran berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data pengukuran: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    // ==================== READING DATA - I_READING_ATAS ====================

    /**
     * Get all I_reading_atas data
     * GET /rightpiezo/ireading
     */
    public function ireading()
    {
        try {
            $model = new I_reading_atas();
            $data = $model->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data I_reading_atas berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data I_reading_atas: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get I_reading_atas by pengukuran ID
     * GET /rightpiezo/ireading/by_pengukuran/{id_pengukuran}
     */
    public function ireadingByPengukuran($id_pengukuran = null)
    {
        try {
            $model = new I_reading_atas();
            $data = $model->where('id_pengukuran', $id_pengukuran)->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data I_reading_atas untuk pengukuran ID ' . $id_pengukuran . ' berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data I_reading_atas: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get I_reading_atas by titik piezometer
     * GET /rightpiezo/ireading/by_titik/{titik}
     */
    public function ireadingByTitik($titik = null)
    {
        try {
            $model = new I_reading_atas();
            $data = $model->where('titik_piezometer', $titik)->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data I_reading_atas untuk titik ' . $titik . ' berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data I_reading_atas: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // ==================== INPUT DATA - T_PEMBACAAN ====================

    /**
     * Get all T_pembacaan data
     * GET /rightpiezo/tpembacaan
     */
    public function tpembacaan()
    {
        try {
            $model = new T_pembacaan();
            $data = $model->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data T_pembacaan berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data T_pembacaan: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get T_pembacaan by pengukuran ID
     * GET /rightpiezo/tpembacaan/by_pengukuran/{id_pengukuran}
     */
    public function tpembacaanByPengukuran($id_pengukuran = null)
    {
        try {
            $model = new T_pembacaan();
            $data = $model->where('id_pengukuran', $id_pengukuran)->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data T_pembacaan untuk pengukuran ID ' . $id_pengukuran . ' berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data T_pembacaan: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get T_pembacaan by lokasi
     * GET /rightpiezo/tpembacaan/by_lokasi/{lokasi}
     */
    public function tpembacaanByLokasi($lokasi = null)
    {
        try {
            $model = new T_pembacaan();
            $data = $model->where('lokasi', $lokasi)->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data T_pembacaan untuk lokasi ' . $lokasi . ' berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data T_pembacaan: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // ==================== CALCULATION DATA - B_PIEZO_METRIK ====================

    /**
     * Get all B_piezo_metrik data
     * GET /rightpiezo/bpiezometrik
     */
    public function bpiezometrik()
    {
        try {
            $model = new B_piezo_metrik();
            $data = $model->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data B_piezo_metrik berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data B_piezo_metrik: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get B_piezo_metrik by pengukuran ID
     * GET /rightpiezo/bpiezometrik/by_pengukuran/{id_pengukuran}
     */
    public function bpiezometrikByPengukuran($id_pengukuran = null)
    {
        try {
            $model = new B_piezo_metrik();
            $data = $model->find($id_pengukuran);

            if (!$data) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Data B_piezo_metrik dengan ID pengukuran ' . $id_pengukuran . ' tidak ditemukan',
                    'data' => null
                ], 404);
            }

            return $this->respond([
                'success' => true,
                'message' => 'Data B_piezo_metrik berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data B_piezo_metrik: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    // ==================== CALCULATION DATA - PERHITUNGAN_PSMETRIK ====================

    /**
     * Get all Perhitungan_t_psmetrik data
     * GET /rightpiezo/perhitunganpsmetrik
     */
    public function perhitunganpsmetrik()
    {
        try {
            $model = new Perhitungan_t_psmetrik();
            $data = $model->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data Perhitungan_t_psmetrik berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data Perhitungan_t_psmetrik: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get Perhitungan_t_psmetrik by pengukuran ID
     * GET /rightpiezo/perhitunganpsmetrik/by_pengukuran/{id_pengukuran}
     */
    public function perhitunganpsmetrikByPengukuran($id_pengukuran = null)
    {
        try {
            $model = new Perhitungan_t_psmetrik();
            $data = $model->find($id_pengukuran);

            if (!$data) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Data Perhitungan_t_psmetrik dengan ID pengukuran ' . $id_pengukuran . ' tidak ditemukan',
                    'data' => null
                ], 404);
            }

            return $this->respond([
                'success' => true,
                'message' => 'Data Perhitungan_t_psmetrik berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data Perhitungan_t_psmetrik: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    // ==================== DETAIL DATA BY PENGUKURAN ID ====================

    /**
     * Get complete data by pengukuran ID
     * GET /rightpiezo/detail/{id_pengukuran}
     */
public function detail($id_pengukuran = null)
{
    try {
        // Load all models
        $pengukuranModel = new T_pengukuran_rightpiez();
        $ireadingModel = new I_reading_atas();
        $tpembacaanModel = new T_pembacaan();
        $bpiezoModel = new B_piezo_metrik();
        $perhitunganModel = new Perhitungan_t_psmetrik();

        // Get main pengukuran data
        $pengukuran = $pengukuranModel->find($id_pengukuran);
        
        if (!$pengukuran) {
            return $this->respond([
                'success' => false,
                'message' => 'Data pengukuran dengan ID ' . $id_pengukuran . ' tidak ditemukan',
                'data' => null
            ], 404);
        }

        // Get all related data (TANPA i_reading_atas)
        $data = [
            'pengukuran' => $pengukuran,
            't_pembacaan' => $tpembacaanModel->where('id_pengukuran', $id_pengukuran)->findAll(),
            'b_piezo_metrik' => $bpiezoModel->find($id_pengukuran),
            'perhitungan_t_psmetrik' => $perhitunganModel->find($id_pengukuran)
        ];

        return $this->respond([
            'success' => true,
            'message' => 'Detail data right piezometer berhasil diambil',
            'data' => $data
        ]);
    } catch (\Exception $e) {
        return $this->respond([
            'success' => false,
            'message' => 'Gagal mengambil detail data: ' . $e->getMessage(),
            'data' => null
        ], 500);
    }
}
    // ==================== ALL DATA ====================

    /**
     * Get all data from all tables
     * GET /rightpiezo/all
     */
    public function all()
    {
        try {
            // Load all models
            $pengukuranModel = new T_pengukuran_rightpiez();
            $ireadingModel = new I_reading_atas();
            $tpembacaanModel = new T_pembacaan();
            $bpiezoModel = new B_piezo_metrik();
            $perhitunganModel = new Perhitungan_t_psmetrik();

            $data = [
                't_pengukuran_rightpiez' => $pengukuranModel->findAll(),
                'i_reading_atas' => $ireadingModel->findAll(),
                't_pembacaan' => $tpembacaanModel->findAll(),
                'b_piezo_metrik' => $bpiezoModel->findAll(),
                'perhitungan_t_psmetrik' => $perhitunganModel->findAll()
            ];

            return $this->respond([
                'success' => true,
                'message' => 'Semua data right piezometer berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil semua data: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // ==================== SYNC DATA ====================

    /**
     * Sync data with last_sync parameter
     * GET /rightpiezo/sync?last_sync=2024-01-01 00:00:00
     */
    public function sync()
    {
        try {
            $lastSync = $this->request->getGet('last_sync');
            
            // Load all models
            $pengukuranModel = new T_pengukuran_rightpiez();
            $ireadingModel = new I_reading_atas();
            $tpembacaanModel = new T_pembacaan();
            $bpiezoModel = new B_piezo_metrik();
            $perhitunganModel = new Perhitungan_t_psmetrik();

            $syncData = [];

            if ($lastSync) {
                // Sync only updated data
                $syncData = [
                    't_pengukuran_rightpiez' => $pengukuranModel->where('updated_at >=', $lastSync)->findAll(),
                    'i_reading_atas' => $ireadingModel->where('updated_at >=', $lastSync)->findAll(),
                    't_pembacaan' => $tpembacaanModel->where('updated_at >=', $lastSync)->findAll(),
                    'b_piezo_metrik' => $bpiezoModel->where('updated_at >=', $lastSync)->findAll(),
                    'perhitungan_t_psmetrik' => $perhitunganModel->where('updated_at >=', $lastSync)->findAll()
                ];
            } else {
                // Sync all data
                $syncData = [
                    't_pengukuran_rightpiez' => $pengukuranModel->findAll(),
                    'i_reading_atas' => $ireadingModel->findAll(),
                    't_pembacaan' => $tpembacaanModel->findAll(),
                    'b_piezo_metrik' => $bpiezoModel->findAll(),
                    'perhitungan_t_psmetrik' => $perhitunganModel->findAll()
                ];
            }

            return $this->respond([
                'success' => true,
                'message' => 'Data sync right piezometer berhasil diambil',
                'last_sync' => date('Y-m-d H:i:s'),
                'data' => $syncData
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal sync data: ' . $e->getMessage(),
                'last_sync' => null,
                'data' => []
            ], 500);
        }
    }

    // ==================== HEALTH CHECK ====================

    /**
     * Health check endpoint
     * GET /rightpiezo/health
     */
    public function health()
    {
        try {
            $pengukuranModel = new T_pengukuran_rightpiez();
            $ireadingModel = new I_reading_atas();
            $tpembacaanModel = new T_pembacaan();
            $bpiezoModel = new B_piezo_metrik();
            $perhitunganModel = new Perhitungan_t_psmetrik();

            $counts = [
                't_pengukuran_rightpiez' => $pengukuranModel->countAll(),
                'i_reading_atas' => $ireadingModel->countAll(),
                't_pembacaan' => $tpembacaanModel->countAll(),
                'b_piezo_metrik' => $bpiezoModel->countAll(),
                'perhitungan_t_psmetrik' => $perhitunganModel->countAll()
            ];

            return $this->respond([
                'success' => true,
                'message' => 'API Right Piezometer sehat',
                'data' => [
                    'server_time' => date('Y-m-d H:i:s'),
                    'table_counts' => $counts,
                    'api_version' => '1.0.0',
                    'status' => 'operational'
                ]
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'API Right Piezometer tidak sehat: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // ==================== LATEST DATA ====================

    /**
     * Get latest pengukuran data
     * GET /rightpiezo/latest
     */
    public function latest()
    {
        try {
            $model = new T_pengukuran_rightpiez();
            $latestData = $model->orderBy('tanggal', 'DESC')
                               ->orderBy('id_pengukuran', 'DESC')
                               ->first();

            return $this->respond([
                'success' => true,
                'message' => 'Data terbaru berhasil diambil',
                'data' => $latestData
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data terbaru: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    // ==================== DATA BY DATE RANGE ====================

    /**
     * Get data by date range
     * GET /rightpiezo/by_date?start=2024-01-01&end=2024-01-31
     */
    public function byDate()
    {
        try {
            $startDate = $this->request->getGet('start');
            $endDate = $this->request->getGet('end');

            if (!$startDate || !$endDate) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Parameter start dan end date diperlukan',
                    'data' => []
                ], 400);
            }

            $model = new T_pengukuran_rightpiez();
            $data = $model->where('tanggal >=', $startDate)
                         ->where('tanggal <=', $endDate)
                         ->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data berdasarkan rentang tanggal berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data by date: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // ==================== DATA STATISTICS ====================

    /**
     * Get data statistics
     * GET /rightpiezo/statistics
     */
    public function statistics()
    {
        try {
            $pengukuranModel = new T_pengukuran_rightpiez();
            $ireadingModel = new I_reading_atas();
            $tpembacaanModel = new T_pembacaan();

            $stats = [
                'total_pengukuran' => $pengukuranModel->countAll(),
                'total_ireading' => $ireadingModel->countAll(),
                'total_tpembacaan' => $tpembacaanModel->countAll(),
                'latest_pengukuran_date' => $pengukuranModel->selectMax('tanggal')->first()['tanggal'] ?? null,
                'years_available' => $pengukuranModel->distinct()->select('tahun')->findAll()
            ];

            return $this->respond([
                'success' => true,
                'message' => 'Statistik data berhasil diambil',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}