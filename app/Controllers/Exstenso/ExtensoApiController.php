<?php

namespace App\Controllers\Exstenso;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Exstenso\PengukuranEksModel;
use App\Models\Exstenso\PembacaanEx1Model;
use App\Models\Exstenso\PembacaanEx2Model;
use App\Models\Exstenso\PembacaanEx3Model;
use App\Models\Exstenso\PembacaanEx4Model;
use App\Models\Exstenso\DeformasiEx1Model;
use App\Models\Exstenso\DeformasiEx2Model;
use App\Models\Exstenso\DeformasiEx3Model;
use App\Models\Exstenso\DeformasiEx4Model;
use App\Models\Exstenso\ReadingsEx1Model;
use App\Models\Exstenso\ReadingsEx2Model;
use App\Models\Exstenso\ReadingsEx3Model;
use App\Models\Exstenso\ReadingsEx4Model;

class ExtensoApiController extends BaseController
{
    use ResponseTrait;

// === DETAIL DATA EXSTENSO BY ID ===
public function detail($id)
{
    try {
        $pengukuranModel = new PengukuranEksModel();
        
        // Pembacaan
        $pembacaanEx1Model = new PembacaanEx1Model();
        $pembacaanEx2Model = new PembacaanEx2Model();
        $pembacaanEx3Model = new PembacaanEx3Model();
        $pembacaanEx4Model = new PembacaanEx4Model();
        
        // Deformasi
        $deformasiEx1Model = new DeformasiEx1Model();
        $deformasiEx2Model = new DeformasiEx2Model();
        $deformasiEx3Model = new DeformasiEx3Model();
        $deformasiEx4Model = new DeformasiEx4Model();
        
        // Readings
        $readingsEx1Model = new ReadingsEx1Model();
        $readingsEx2Model = new ReadingsEx2Model();
        $readingsEx3Model = new ReadingsEx3Model();
        $readingsEx4Model = new ReadingsEx4Model();

        // Cari data pengukuran utama
        $pengukuran = $pengukuranModel->find($id);
        
        if (!$pengukuran) {
            return $this->respond([
                'status' => false,
                'message' => 'Data pengukuran dengan ID ' . $id . ' tidak ditemukan',
                'data' => null
            ], 404);
        }

        // Format data sesuai dengan yang diharapkan Android
        $data = [
            'pengukuran' => $pengukuran,
            
            // PEMBACAAN - format array dengan sensor_name
            'pembacaan' => [
                array_merge(
                    $pembacaanEx1Model->where('id_pengukuran', $id)->first() ?? [],
                    ['sensor_name' => 'EX1']
                ),
                array_merge(
                    $pembacaanEx2Model->where('id_pengukuran', $id)->first() ?? [],
                    ['sensor_name' => 'EX2']
                ),
                array_merge(
                    $pembacaanEx3Model->where('id_pengukuran', $id)->first() ?? [],
                    ['sensor_name' => 'EX3']
                ),
                array_merge(
                    $pembacaanEx4Model->where('id_pengukuran', $id)->first() ?? [],
                    ['sensor_name' => 'EX4']
                )
            ],
            
            // DEFORMASI - format array dengan sensor_name
            'deformasi' => [
                array_merge(
                    $deformasiEx1Model->where('id_pengukuran', $id)->first() ?? [],
                    ['sensor_name' => 'EX1']
                ),
                array_merge(
                    $deformasiEx2Model->where('id_pengukuran', $id)->first() ?? [],
                    ['sensor_name' => 'EX2']
                ),
                array_merge(
                    $deformasiEx3Model->where('id_pengukuran', $id)->first() ?? [],
                    ['sensor_name' => 'EX3']
                ),
                array_merge(
                    $deformasiEx4Model->where('id_pengukuran', $id)->first() ?? [],
                    ['sensor_name' => 'EX4']
                )
            ],
            
            // READINGS - format array dengan sensor_name
            'readings' => [
                array_merge(
                    $readingsEx1Model->where('id_pengukuran', $id)->first() ?? [],
                    ['sensor_name' => 'EX1']
                ),
                array_merge(
                    $readingsEx2Model->where('id_pengukuran', $id)->first() ?? [],
                    ['sensor_name' => 'EX2']
                ),
                array_merge(
                    $readingsEx3Model->where('id_pengukuran', $id)->first() ?? [],
                    ['sensor_name' => 'EX3']
                ),
                array_merge(
                    $readingsEx4Model->where('id_pengukuran', $id)->first() ?? [],
                    ['sensor_name' => 'EX4']
                )
            ]
        ];

        // Hapus elemen array yang kosong (jika tidak ada data)
        $data['pembacaan'] = array_filter($data['pembacaan'], function($item) {
            return !empty($item) && count($item) > 1; // lebih dari 1 karena ada sensor_name
        });
        
        $data['deformasi'] = array_filter($data['deformasi'], function($item) {
            return !empty($item) && count($item) > 1;
        });
        
        $data['readings'] = array_filter($data['readings'], function($item) {
            return !empty($item) && count($item) > 1;
        });

        return $this->respond([
            'status' => true,
            'message' => 'Detail data extenso dengan ID ' . $id . ' berhasil diambil',
            'data' => $data
        ]);
    } catch (\Exception $e) {
        return $this->respond([
            'status' => false,
            'message' => 'Gagal mengambil detail data: ' . $e->getMessage(),
            'data' => null
        ], 500);
    }
}

    // === PENGUKURAN EKSTENSO ===
    public function pengukuranEks()
    {
        try {
            $model = new PengukuranEksModel();
            $data = $model->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Data pengukuran extenso berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil data pengukuran: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // === PEMBACAAN EX1-EX4 ===
    public function pembacaanEx1()
    {
        try {
            $model = new PembacaanEx1Model();
            $data = $model->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Data pembacaan Ex1 berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil data pembacaan Ex1: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function pembacaanEx2()
    {
        try {
            $model = new PembacaanEx2Model();
            $data = $model->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Data pembacaan Ex2 berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil data pembacaan Ex2: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function pembacaanEx3()
    {
        try {
            $model = new PembacaanEx3Model();
            $data = $model->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Data pembacaan Ex3 berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil data pembacaan Ex3: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function pembacaanEx4()
    {
        try {
            $model = new PembacaanEx4Model();
            $data = $model->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Data pembacaan Ex4 berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil data pembacaan Ex4: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // === DEFORMASI EX1-EX4 ===
    public function deformasiEx1()
    {
        try {
            $model = new DeformasiEx1Model();
            $data = $model->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Data deformasi Ex1 berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil data deformasi Ex1: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function deformasiEx2()
    {
        try {
            $model = new DeformasiEx2Model();
            $data = $model->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Data deformasi Ex2 berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil data deformasi Ex2: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function deformasiEx3()
    {
        try {
            $model = new DeformasiEx3Model();
            $data = $model->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Data deformasi Ex3 berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil data deformasi Ex3: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function deformasiEx4()
    {
        try {
            $model = new DeformasiEx4Model();
            $data = $model->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Data deformasi Ex4 berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil data deformasi Ex4: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // === READINGS EX1-EX4 ===
    public function readingsEx1()
    {
        try {
            $model = new ReadingsEx1Model();
            $data = $model->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Data readings Ex1 berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil data readings Ex1: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function readingsEx2()
    {
        try {
            $model = new ReadingsEx2Model();
            $data = $model->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Data readings Ex2 berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil data readings Ex2: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function readingsEx3()
    {
        try {
            $model = new ReadingsEx3Model();
            $data = $model->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Data readings Ex3 berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil data readings Ex3: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function readingsEx4()
    {
        try {
            $model = new ReadingsEx4Model();
            $data = $model->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Data readings Ex4 berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil data readings Ex4: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // === ALL DATA EXSTENSO ===
    public function allData()
    {
        try {
            $pengukuranModel = new PengukuranEksModel();
            
            // Pembacaan
            $pembacaanEx1Model = new PembacaanEx1Model();
            $pembacaanEx2Model = new PembacaanEx2Model();
            $pembacaanEx3Model = new PembacaanEx3Model();
            $pembacaanEx4Model = new PembacaanEx4Model();
            
            // Deformasi
            $deformasiEx1Model = new DeformasiEx1Model();
            $deformasiEx2Model = new DeformasiEx2Model();
            $deformasiEx3Model = new DeformasiEx3Model();
            $deformasiEx4Model = new DeformasiEx4Model();
            
            // Readings
            $readingsEx1Model = new ReadingsEx1Model();
            $readingsEx2Model = new ReadingsEx2Model();
            $readingsEx3Model = new ReadingsEx3Model();
            $readingsEx4Model = new ReadingsEx4Model();

            $data = [
                'pengukuran' => $pengukuranModel->findAll(),
                
                // Pembacaan
                'pembacaan_ex1' => $pembacaanEx1Model->findAll(),
                'pembacaan_ex2' => $pembacaanEx2Model->findAll(),
                'pembacaan_ex3' => $pembacaanEx3Model->findAll(),
                'pembacaan_ex4' => $pembacaanEx4Model->findAll(),
                
                // Deformasi
                'deformasi_ex1' => $deformasiEx1Model->findAll(),
                'deformasi_ex2' => $deformasiEx2Model->findAll(),
                'deformasi_ex3' => $deformasiEx3Model->findAll(),
                'deformasi_ex4' => $deformasiEx4Model->findAll(),
                
                // Readings
                'readings_ex1' => $readingsEx1Model->findAll(),
                'readings_ex2' => $readingsEx2Model->findAll(),
                'readings_ex3' => $readingsEx3Model->findAll(),
                'readings_ex4' => $readingsEx4Model->findAll()
            ];

            return $this->respond([
                'status' => true,
                'message' => 'Semua data extenso berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil semua data: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // === DATA BY PENGUKURAN ID ===
    public function byPengukuran($id_pengukuran)
    {
        try {
            $pengukuranModel = new PengukuranEksModel();
            
            // Pembacaan
            $pembacaanEx1Model = new PembacaanEx1Model();
            $pembacaanEx2Model = new PembacaanEx2Model();
            $pembacaanEx3Model = new PembacaanEx3Model();
            $pembacaanEx4Model = new PembacaanEx4Model();
            
            // Deformasi
            $deformasiEx1Model = new DeformasiEx1Model();
            $deformasiEx2Model = new DeformasiEx2Model();
            $deformasiEx3Model = new DeformasiEx3Model();
            $deformasiEx4Model = new DeformasiEx4Model();
            
            // Readings
            $readingsEx1Model = new ReadingsEx1Model();
            $readingsEx2Model = new ReadingsEx2Model();
            $readingsEx3Model = new ReadingsEx3Model();
            $readingsEx4Model = new ReadingsEx4Model();

            $data = [
                'pengukuran' => $pengukuranModel->find($id_pengukuran),
                
                // Pembacaan
                'pembacaan_ex1' => $pembacaanEx1Model->where('id_pengukuran', $id_pengukuran)->findAll(),
                'pembacaan_ex2' => $pembacaanEx2Model->where('id_pengukuran', $id_pengukuran)->findAll(),
                'pembacaan_ex3' => $pembacaanEx3Model->where('id_pengukuran', $id_pengukuran)->findAll(),
                'pembacaan_ex4' => $pembacaanEx4Model->where('id_pengukuran', $id_pengukuran)->findAll(),
                
                // Deformasi
                'deformasi_ex1' => $deformasiEx1Model->where('id_pengukuran', $id_pengukuran)->findAll(),
                'deformasi_ex2' => $deformasiEx2Model->where('id_pengukuran', $id_pengukuran)->findAll(),
                'deformasi_ex3' => $deformasiEx3Model->where('id_pengukuran', $id_pengukuran)->findAll(),
                'deformasi_ex4' => $deformasiEx4Model->where('id_pengukuran', $id_pengukuran)->findAll(),
                
                // Readings
                'readings_ex1' => $readingsEx1Model->where('id_pengukuran', $id_pengukuran)->findAll(),
                'readings_ex2' => $readingsEx2Model->where('id_pengukuran', $id_pengukuran)->findAll(),
                'readings_ex3' => $readingsEx3Model->where('id_pengukuran', $id_pengukuran)->findAll(),
                'readings_ex4' => $readingsEx4Model->where('id_pengukuran', $id_pengukuran)->findAll()
            ];

            return $this->respond([
                'status' => true,
                'message' => 'Data extenso untuk pengukuran ID ' . $id_pengukuran . ' berhasil diambil',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil data by pengukuran: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // === SYNC DATA EXSTENSO ===
    public function sync()
    {
        try {
            $lastSync = $this->request->getGet('last_sync');
            
            $pengukuranModel = new PengukuranEksModel();
            
            // Pembacaan
            $pembacaanEx1Model = new PembacaanEx1Model();
            $pembacaanEx2Model = new PembacaanEx2Model();
            $pembacaanEx3Model = new PembacaanEx3Model();
            $pembacaanEx4Model = new PembacaanEx4Model();
            
            // Deformasi
            $deformasiEx1Model = new DeformasiEx1Model();
            $deformasiEx2Model = new DeformasiEx2Model();
            $deformasiEx3Model = new DeformasiEx3Model();
            $deformasiEx4Model = new DeformasiEx4Model();
            
            // Readings
            $readingsEx1Model = new ReadingsEx1Model();
            $readingsEx2Model = new ReadingsEx2Model();
            $readingsEx3Model = new ReadingsEx3Model();
            $readingsEx4Model = new ReadingsEx4Model();

            $syncData = [];

            if ($lastSync) {
                $syncData = [
                    'pengukuran' => $pengukuranModel->where('updated_at >=', $lastSync)->findAll(),
                    
                    // Pembacaan
                    'pembacaan_ex1' => $pembacaanEx1Model->where('updated_at >=', $lastSync)->findAll(),
                    'pembacaan_ex2' => $pembacaanEx2Model->where('updated_at >=', $lastSync)->findAll(),
                    'pembacaan_ex3' => $pembacaanEx3Model->where('updated_at >=', $lastSync)->findAll(),
                    'pembacaan_ex4' => $pembacaanEx4Model->where('updated_at >=', $lastSync)->findAll(),
                    
                    // Deformasi
                    'deformasi_ex1' => $deformasiEx1Model->where('updated_at >=', $lastSync)->findAll(),
                    'deformasi_ex2' => $deformasiEx2Model->where('updated_at >=', $lastSync)->findAll(),
                    'deformasi_ex3' => $deformasiEx3Model->where('updated_at >=', $lastSync)->findAll(),
                    'deformasi_ex4' => $deformasiEx4Model->where('updated_at >=', $lastSync)->findAll(),
                    
                    // Readings
                    'readings_ex1' => $readingsEx1Model->where('updated_at >=', $lastSync)->findAll(),
                    'readings_ex2' => $readingsEx2Model->where('updated_at >=', $lastSync)->findAll(),
                    'readings_ex3' => $readingsEx3Model->where('updated_at >=', $lastSync)->findAll(),
                    'readings_ex4' => $readingsEx4Model->where('updated_at >=', $lastSync)->findAll()
                ];
            } else {
                $syncData = [
                    'pengukuran' => $pengukuranModel->findAll(),
                    
                    // Pembacaan
                    'pembacaan_ex1' => $pembacaanEx1Model->findAll(),
                    'pembacaan_ex2' => $pembacaanEx2Model->findAll(),
                    'pembacaan_ex3' => $pembacaanEx3Model->findAll(),
                    'pembacaan_ex4' => $pembacaanEx4Model->findAll(),
                    
                    // Deformasi
                    'deformasi_ex1' => $deformasiEx1Model->findAll(),
                    'deformasi_ex2' => $deformasiEx2Model->findAll(),
                    'deformasi_ex3' => $deformasiEx3Model->findAll(),
                    'deformasi_ex4' => $deformasiEx4Model->findAll(),
                    
                    // Readings
                    'readings_ex1' => $readingsEx1Model->findAll(),
                    'readings_ex2' => $readingsEx2Model->findAll(),
                    'readings_ex3' => $readingsEx3Model->findAll(),
                    'readings_ex4' => $readingsEx4Model->findAll()
                ];
            }

            return $this->respond([
                'status' => true,
                'message' => 'Data sync extenso berhasil diambil',
                'last_sync' => date('Y-m-d H:i:s'),
                'data' => $syncData
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal sync data: ' . $e->getMessage(),
                'last_sync' => null,
                'data' => []
            ], 500);
        }
    }

    // === HEALTH CHECK ===
    public function health()
    {
        try {
            $pengukuranModel = new PengukuranEksModel();
            $count = $pengukuranModel->countAll();

            return $this->respond([
                'status' => true,
                'message' => 'API Extenso sehat',
                'data' => [
                    'server_time' => date('Y-m-d H:i:s'),
                    'total_pengukuran' => $count,
                    'api_version' => '1.0.0'
                ]
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'API Extenso tidak sehat: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // === GET LATEST DATA ===
    public function latest()
    {
        try {
            $pengukuranModel = new PengukuranEksModel();
            $latestData = $pengukuranModel->orderBy('created_at', 'DESC')->first();

            return $this->respond([
                'status' => true,
                'message' => 'Data terbaru berhasil diambil',
                'data' => $latestData
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal mengambil data terbaru: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}