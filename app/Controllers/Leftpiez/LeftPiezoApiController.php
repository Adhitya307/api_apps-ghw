<?php

namespace App\Controllers\Leftpiez;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\LeftPiez\TPengukuranLeftpiezModel;
use App\Models\LeftPiez\IreadingA;
use App\Models\LeftPiez\IreadingB;
use App\Models\LeftPiez\TPembacaanLeftPiezModel;
use App\Models\LeftPiez\MetrikModel;
use App\Models\LeftPiez\PerhitunganLeftPiezModel;

class LeftPiezoApiController extends BaseController
{
    use ResponseTrait;

    // ==================== MASTER DATA - PENGUKURAN ====================
    
    /**
     * Get all pengukuran data
     * GET /leftpiezo/pengukuran-leftpiez
     */
    public function pengukuran()
    {
        try {
            $model = new TPengukuranLeftpiezModel();
            $data = $model->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data pengukuran left piezometer berhasil diambil',
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
     * GET /leftpiezo/pengukuran-leftpiez/{id}
     */
    public function pengukuranById($id = null)
    {
        try {
            $model = new TPengukuranLeftpiezModel();
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

    // ==================== READING DATA - I_READING_A ====================

    /**
     * Get all I_Reading_A data
     * GET /leftpiezo/ireading-a
     */
    public function ireadingA()
    {
        try {
            $model = new IreadingA();
            $data = $model->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data I_Reading_A berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data I_Reading_A: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get I_Reading_A by pengukuran ID
     * GET /leftpiezo/ireading-a/by_pengukuran/{id_pengukuran}
     */
    public function ireadingAByPengukuran($id_pengukuran = null)
    {
        try {
            $model = new IreadingA();
            $data = $model->where('id_pengukuran', $id_pengukuran)->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data I_Reading_A untuk pengukuran ID ' . $id_pengukuran . ' berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data I_Reading_A: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get I_Reading_A by titik piezometer
     * GET /leftpiezo/ireading-a/by_titik/{titik}
     */
    public function ireadingAByTitik($titik = null)
    {
        try {
            $model = new IreadingA();
            $data = $model->where('titik_piezometer', $titik)->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data I_Reading_A untuk titik ' . $titik . ' berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data I_Reading_A: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // ==================== READING DATA - I_READING_B ====================

    /**
     * Get all I_Reading_B data
     * GET /leftpiezo/ireading-b
     */
    public function ireadingB()
    {
        try {
            $model = new IreadingB();
            $data = $model->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data I_Reading_B berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data I_Reading_B: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get I_Reading_B by pengukuran ID
     * GET /leftpiezo/ireading-b/by_pengukuran/{id_pengukuran}
     */
    public function ireadingBByPengukuran($id_pengukuran = null)
    {
        try {
            $model = new IreadingB();
            $data = $model->where('id_pengukuran', $id_pengukuran)->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data I_Reading_B untuk pengukuran ID ' . $id_pengukuran . ' berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data I_Reading_B: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get I_Reading_B by titik piezometer
     * GET /leftpiezo/ireading-b/by_titik/{titik}
     */
    public function ireadingBByTitik($titik = null)
    {
        try {
            $model = new IreadingB();
            $data = $model->where('titik_piezometer', $titik)->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data I_Reading_B untuk titik ' . $titik . ' berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data I_Reading_B: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // ==================== INPUT DATA - T_PEMBACAAN ====================

    /**
     * Get all T_Pembacaan data
     * GET /leftpiezo/tpembacaan
     */
    public function tpembacaan()
    {
        try {
            $model = new TPembacaanLeftPiezModel();
            $data = $model->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data T_Pembacaan berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data T_Pembacaan: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get T_Pembacaan by pengukuran ID
     * GET /leftpiezo/tpembacaan/by_pengukuran/{id_pengukuran}
     */
    public function tpembacaanByPengukuran($id_pengukuran = null)
    {
        try {
            $model = new TPembacaanLeftPiezModel();
            $data = $model->where('id_pengukuran', $id_pengukuran)->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data T_Pembacaan untuk pengukuran ID ' . $id_pengukuran . ' berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data T_Pembacaan: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get T_Pembacaan by tipe piezometer
     * GET /leftpiezo/tpembacaan/by_tipe/{tipe}
     */
    public function tpembacaanByTipe($tipe = null)
    {
        try {
            $model = new TPembacaanLeftPiezModel();
            $data = $model->where('tipe_piezometer', $tipe)->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data T_Pembacaan untuk tipe ' . $tipe . ' berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data T_Pembacaan: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // ==================== CALCULATION DATA - B_PIEZO_METRIK ====================

    /**
     * Get all B_Piezo_Metrik data
     * GET /leftpiezo/bpiezo-metrik
     */
    public function bpiezometrik()
    {
        try {
            $model = new MetrikModel();
            $data = $model->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data B_Piezo_Metrik berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data B_Piezo_Metrik: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get B_Piezo_Metrik by pengukuran ID
     * GET /leftpiezo/bpiezo-metrik/by_pengukuran/{id_pengukuran}
     */
    public function bpiezometrikByPengukuran($id_pengukuran = null)
    {
        try {
            $model = new MetrikModel();
            $data = $model->where('id_pengukuran', $id_pengukuran)->first();

            if (!$data) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Data B_Piezo_Metrik dengan ID pengukuran ' . $id_pengukuran . ' tidak ditemukan',
                    'data' => null
                ], 404);
            }

            return $this->respond([
                'success' => true,
                'message' => 'Data B_Piezo_Metrik berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data B_Piezo_Metrik: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    // ==================== CALCULATION DATA - PERHITUNGAN_LEFT_PIEZ ====================

    /**
     * Get all Perhitungan_Left_Piez data
     * GET /leftpiezo/perhitungan-leftpiez
     */
    public function perhitunganleftpiez()
    {
        try {
            $model = new PerhitunganLeftPiezModel();
            $data = $model->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data Perhitungan_Left_Piez berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data Perhitungan_Left_Piez: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get Perhitungan_Left_Piez by pengukuran ID
     * GET /leftpiezo/perhitungan-leftpiez/by_pengukuran/{id_pengukuran}
     */
    public function perhitunganleftpiezByPengukuran($id_pengukuran = null)
    {
        try {
            $model = new PerhitunganLeftPiezModel();
            $data = $model->where('id_pengukuran', $id_pengukuran)->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data Perhitungan_Left_Piez untuk pengukuran ID ' . $id_pengukuran . ' berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data Perhitungan_Left_Piez: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get Perhitungan_Left_Piez by tipe piezometer
     * GET /leftpiezo/perhitungan-leftpiez/by_tipe/{tipe}
     */
    public function perhitunganleftpiezByTipe($tipe = null)
    {
        try {
            $model = new PerhitunganLeftPiezModel();
            $data = $model->where('tipe_piezometer', $tipe)->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data Perhitungan_Left_Piez untuk tipe ' . $tipe . ' berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data Perhitungan_Left_Piez: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // ==================== DETAIL DATA BY PENGUKURAN ID ====================

    /**
     * Get complete data by pengukuran ID
     * GET /leftpiezo/detail/{id_pengukuran}
     */
    public function detail($id_pengukuran = null)
    {
        try {
            // Load all models
            $pengukuranModel = new TPengukuranLeftpiezModel();
            $ireadingAModel = new IreadingA();
            $ireadingBModel = new IreadingB();
            $tpembacaanModel = new TPembacaanLeftPiezModel();
            $bpiezoModel = new MetrikModel();
            $perhitunganModel = new PerhitunganLeftPiezModel();

            // Get main pengukuran data
            $pengukuran = $pengukuranModel->find($id_pengukuran);
            
            if (!$pengukuran) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Data pengukuran dengan ID ' . $id_pengukuran . ' tidak ditemukan',
                    'data' => null
                ], 404);
            }

            // Get all related data
            $data = [
                'pengukuran' => $pengukuran,
                'i_reading_a' => $ireadingAModel->where('id_pengukuran', $id_pengukuran)->findAll(),
                'i_reading_b' => $ireadingBModel->where('id_pengukuran', $id_pengukuran)->findAll(),
                't_pembacaan' => $tpembacaanModel->where('id_pengukuran', $id_pengukuran)->findAll(),
                'b_piezo_metrik' => $bpiezoModel->where('id_pengukuran', $id_pengukuran)->first(),
                'perhitungan_left_piez' => $perhitunganModel->where('id_pengukuran', $id_pengukuran)->findAll()
            ];

            return $this->respond([
                'success' => true,
                'message' => 'Detail data left piezometer berhasil diambil',
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
     * GET /leftpiezo/all
     */
    public function all()
    {
        try {
            // Load all models
            $pengukuranModel = new TPengukuranLeftpiezModel();
            $ireadingAModel = new IreadingA();
            $ireadingBModel = new IreadingB();
            $tpembacaanModel = new TPembacaanLeftPiezModel();
            $bpiezoModel = new MetrikModel();
            $perhitunganModel = new PerhitunganLeftPiezModel();

            $data = [
                't_pengukuran_leftpiez' => $pengukuranModel->findAll(),
                'i_reading_A_all' => $ireadingAModel->findAll(),
                'i_reading_B_all' => $ireadingBModel->findAll(),
                't_pembacaan_left_piez' => $tpembacaanModel->findAll(),
                'b_piezo_metrik' => $bpiezoModel->findAll(),
                'perhitungan_left_piez' => $perhitunganModel->findAll()
            ];

            return $this->respond([
                'success' => true,
                'message' => 'Semua data left piezometer berhasil diambil',
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
     * GET /leftpiezo/sync?last_sync=2024-01-01 00:00:00
     */
    public function sync()
    {
        try {
            $lastSync = $this->request->getGet('last_sync');
            
            // Load all models
            $pengukuranModel = new TPengukuranLeftpiezModel();
            $ireadingAModel = new IreadingA();
            $ireadingBModel = new IreadingB();
            $tpembacaanModel = new TPembacaanLeftPiezModel();
            $bpiezoModel = new MetrikModel();
            $perhitunganModel = new PerhitunganLeftPiezModel();

            $syncData = [];

            if ($lastSync) {
                // Sync only updated data
                $syncData = [
                    't_pengukuran_leftpiez' => $pengukuranModel->where('updated_at >=', $lastSync)->findAll(),
                    'i_reading_A_all' => $ireadingAModel->where('updated_at >=', $lastSync)->findAll(),
                    'i_reading_B_all' => $ireadingBModel->where('updated_at >=', $lastSync)->findAll(),
                    't_pembacaan_left_piez' => $tpembacaanModel->where('updated_at >=', $lastSync)->findAll(),
                    'b_piezo_metrik' => $bpiezoModel->where('updated_at >=', $lastSync)->findAll(),
                    'perhitungan_left_piez' => $perhitunganModel->where('updated_at >=', $lastSync)->findAll()
                ];
            } else {
                // Sync all data
                $syncData = [
                    't_pengukuran_leftpiez' => $pengukuranModel->findAll(),
                    'i_reading_A_all' => $ireadingAModel->findAll(),
                    'i_reading_B_all' => $ireadingBModel->findAll(),
                    't_pembacaan_left_piez' => $tpembacaanModel->findAll(),
                    'b_piezo_metrik' => $bpiezoModel->findAll(),
                    'perhitungan_left_piez' => $perhitunganModel->findAll()
                ];
            }

            return $this->respond([
                'success' => true,
                'message' => 'Data sync left piezometer berhasil diambil',
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
     * GET /leftpiezo/health
     */
    public function health()
    {
        try {
            $pengukuranModel = new TPengukuranLeftpiezModel();
            $ireadingAModel = new IreadingA();
            $ireadingBModel = new IreadingB();
            $tpembacaanModel = new TPembacaanLeftPiezModel();
            $bpiezoModel = new MetrikModel();
            $perhitunganModel = new PerhitunganLeftPiezModel();

            $counts = [
                't_pengukuran_leftpiez' => $pengukuranModel->countAll(),
                'i_reading_A_all' => $ireadingAModel->countAll(),
                'i_reading_B_all' => $ireadingBModel->countAll(),
                't_pembacaan_left_piez' => $tpembacaanModel->countAll(),
                'b_piezo_metrik' => $bpiezoModel->countAll(),
                'perhitungan_left_piez' => $perhitunganModel->countAll()
            ];

            return $this->respond([
                'success' => true,
                'message' => 'API Left Piezometer sehat',
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
                'message' => 'API Left Piezometer tidak sehat: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // ==================== LATEST DATA ====================

    /**
     * Get latest pengukuran data
     * GET /leftpiezo/latest
     */
    public function latest()
    {
        try {
            $model = new TPengukuranLeftpiezModel();
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
     * GET /leftpiezo/by_date?start=2024-01-01&end=2024-01-31
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

            $model = new TPengukuranLeftpiezModel();
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
     * GET /leftpiezo/statistics
     */
    public function statistics()
    {
        try {
            $pengukuranModel = new TPengukuranLeftpiezModel();
            $ireadingAModel = new IreadingA();
            $ireadingBModel = new IreadingB();
            $tpembacaanModel = new TPembacaanLeftPiezModel();

            $stats = [
                'total_pengukuran' => $pengukuranModel->countAll(),
                'total_ireading_a' => $ireadingAModel->countAll(),
                'total_ireading_b' => $ireadingBModel->countAll(),
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

    // ==================== DATA BY TAHUN ====================

    /**
     * Get data by tahun
     * GET /leftpiezo/by_tahun/{tahun}
     */
    public function byTahun($tahun = null)
    {
        try {
            $model = new TPengukuranLeftpiezModel();
            $data = $model->where('tahun', $tahun)->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data untuk tahun ' . $tahun . ' berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data by tahun: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // ==================== DATA BY PERIODE ====================

    /**
     * Get data by periode
     * GET /leftpiezo/by_periode/{periode}
     */
    public function byPeriode($periode = null)
    {
        try {
            $model = new TPengukuranLeftpiezModel();
            $data = $model->where('periode', $periode)->findAll();

            return $this->respond([
                'success' => true,
                'message' => 'Data untuk periode ' . $periode . ' berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil data by periode: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // ==================== PIEZOMETER POINTS ====================

    /**
     * Get all available piezometer points
     * GET /leftpiezo/piezometer-points
     */
    public function piezometerPoints()
    {
        try {
            $points = [
                'i_reading_a_points' => ['L_01', 'L_02', 'L_03', 'L_04', 'L_05', 'L_06', 'L_07', 'L_08', 'L_09', 'L_10', 'SPZ_02'],
                'i_reading_b_points' => ['L_01', 'L_02', 'L_03', 'L_04', 'L_05', 'L_06', 'L_07', 'L_08', 'L_09', 'L_10', 'SPZ_02'],
                't_pembacaan_types' => ['L01', 'L02', 'L03', 'L04', 'L05', 'L06', 'L07', 'L08', 'L09', 'L10', 'SPZ02'],
                'perhitungan_types' => ['L01', 'L02', 'L03', 'L04', 'L05', 'L06', 'L07', 'L08', 'L09', 'L10', 'SPZ02']
            ];

            return $this->respond([
                'success' => true,
                'message' => 'Daftar titik piezometer berhasil diambil',
                'data' => $points
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal mengambil daftar titik piezometer: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // ==================== BULK DATA INSERT ====================

    /**
     * Insert bulk data (for testing/initial setup)
     * POST /leftpiezo/bulk-insert
     */
    public function bulkInsert()
    {
        try {
            $input = $this->request->getJSON(true);
            
            if (!isset($input['table']) || !isset($input['data'])) {
                return $this->respond([
                    'success' => false,
                    'message' => 'Parameter table dan data diperlukan',
                    'data' => null
                ], 400);
            }

            $table = $input['table'];
            $data = $input['data'];

            // Determine which model to use based on table name
            switch ($table) {
                case 't_pengukuran_leftpiez':
                    $model = new TPengukuranLeftpiezModel();
                    break;
                case 'i_reading_A_all':
                    $model = new IreadingA();
                    break;
                case 'i_reading_B_all':
                    $model = new IreadingB();
                    break;
                case 't_pembacaan_left_piez':
                    $model = new TPembacaanLeftPiezModel();
                    break;
                case 'b_piezo_metrik':
                    $model = new MetrikModel();
                    break;
                case 'perhitungan_left_piez':
                    $model = new PerhitunganLeftPiezModel();
                    break;
                default:
                    return $this->respond([
                        'success' => false,
                        'message' => 'Tabel ' . $table . ' tidak dikenali',
                        'data' => null
                    ], 400);
            }

            $result = $model->insertBatch($data);

            return $this->respond([
                'success' => true,
                'message' => 'Data berhasil diinsert ke tabel ' . $table,
                'data' => ['inserted_rows' => $result]
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Gagal insert bulk data: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}