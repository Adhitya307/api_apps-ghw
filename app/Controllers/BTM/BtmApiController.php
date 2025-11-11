<?php

namespace App\Controllers\Btm;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
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
use App\Models\Btm\ScatterBt1Model;
use App\Models\Btm\ScatterBt2Model;
use App\Models\Btm\ScatterBt3Model;
use App\Models\Btm\ScatterBt4Model;
use App\Models\Btm\ScatterBt6Model;
use App\Models\Btm\ScatterBt7Model;
use App\Models\Btm\ScatterBt8Model;

class BtmApiController extends BaseController
{
    use ResponseTrait;

    // === PENGUKURAN BTM ===
    public function pengukuran()
    {
        $model = new PengukuranBtmModel();
        $data = $model->getAllPengukuran();

        return $this->respond([
            'success' => true,
            'message' => 'Data pengukuran BTM berhasil diambil',
            'data' => $data
        ]);
    }

    // === BACAAAN PER BT ===
    public function bacaan_bt1()
    {
        $model = new BacaanBt1Model();
        $data = $model->getAllBacaan();

        return $this->respond([
            'success' => true,
            'message' => 'Data bacaan BT1 berhasil diambil',
            'data' => $data
        ]);
    }

    public function bacaan_bt2()
    {
        $model = new BacaanBt2Model();
        $data = $model->getAllBacaan();

        return $this->respond([
            'success' => true,
            'message' => 'Data bacaan BT2 berhasil diambil',
            'data' => $data
        ]);
    }

    public function bacaan_bt3()
    {
        $model = new BacaanBt3Model();
        $data = $model->getAllBacaan();

        return $this->respond([
            'success' => true,
            'message' => 'Data bacaan BT3 berhasil diambil',
            'data' => $data
        ]);
    }

    public function bacaan_bt4()
    {
        $model = new BacaanBt4Model();
        $data = $model->getAllBacaan();

        return $this->respond([
            'success' => true,
            'message' => 'Data bacaan BT4 berhasil diambil',
            'data' => $data
        ]);
    }

    public function bacaan_bt5()
    {
        $model = new BacaanBt5Model();
        $data = $model->getAllBacaan();

        return $this->respond([
            'success' => true,
            'message' => 'Data bacaan BT5 berhasil diambil',
            'data' => $data
        ]);
    }

    public function bacaan_bt6()
    {
        $model = new BacaanBt6Model();
        $data = $model->getAllBacaan();

        return $this->respond([
            'success' => true,
            'message' => 'Data bacaan BT6 berhasil diambil',
            'data' => $data
        ]);
    }

    public function bacaan_bt7()
    {
        $model = new BacaanBt7Model();
        $data = $model->getAllBacaan();

        return $this->respond([
            'success' => true,
            'message' => 'Data bacaan BT7 berhasil diambil',
            'data' => $data
        ]);
    }

    public function bacaan_bt8()
    {
        $model = new BacaanBt8Model();
        $data = $model->getAllBacaan();

        return $this->respond([
            'success' => true,
            'message' => 'Data bacaan BT8 berhasil diambil',
            'data' => $data
        ]);
    }

    // === PERHITUNGAN PER BT ===
    public function perhitungan_bt1()
    {
        $model = new PerhitunganBt1Model();
        $data = $model->getAllPerhitunganBt1();

        return $this->respond([
            'success' => true,
            'message' => 'Data perhitungan BT1 berhasil diambil',
            'data' => $data
        ]);
    }

    public function perhitungan_bt2()
    {
        $model = new PerhitunganBt2Model();
        $data = $model->getAllPerhitunganBt2();

        return $this->respond([
            'success' => true,
            'message' => 'Data perhitungan BT2 berhasil diambil',
            'data' => $data
        ]);
    }

    public function perhitungan_bt3()
    {
        $model =  new PerhitunganBt3Model();
        $data = $model->getAllPerhitunganBt3();

        return $this->respond([
            'success' => true,
            'message' => 'Data perhitungan BT3 berhasil diambil',
            'data' => $data
        ]);
    }

    public function perhitungan_bt4()
    {
        $model = new PerhitunganBt4Model();
        $data = $model->getAllPerhitunganBt4();

        return $this->respond([
            'success' => true,
            'message' => 'Data perhitungan BT4 berhasil diambil',
            'data' => $data
        ]);
    }

    public function perhitungan_bt5()
    {
        $model = new PerhitunganBt5Model();
        $data = $model->getAllPerhitunganBt5();

        return $this->respond([
            'success' => true,
            'message' => 'Data perhitungan BT5 berhasil diambil',
            'data' => $data
        ]);
    }

    public function perhitungan_bt6()
    {
        $model = new PerhitunganBt6Model();
        $data = $model->getAllPerhitunganBt6();

        return $this->respond([
            'success' => true,
            'message' => 'Data perhitungan BT6 berhasil diambil',
            'data' => $data
        ]);
    }

    public function perhitungan_bt7()
    {
        $model = new PerhitunganBt7Model();
        $data = $model->getAllPerhitunganBt7();

        return $this->respond([
            'success' => true,
            'message' => 'Data perhitungan BT7 berhasil diambil',
            'data' => $data
        ]);
    }

    public function perhitungan_bt8()
    {
        $model = new PerhitunganBt8Model();
        $data = $model->getAllPerhitunganBt8();

        return $this->respond([
            'success' => true,
            'message' => 'Data perhitungan BT8 berhasil diambil',
            'data' => $data
        ]);
    }

    // === SCATTER PER BT ===
    public function scatter_bt1()
    {
        $model = new ScatterBt1Model();
        $data = $model->getAllForChart();

        return $this->respond([
            'success' => true,
            'message' => 'Data scatter BT1 berhasil diambil',
            'data' => $data
        ]);
    }

    public function scatter_bt2()
    {
        $model = new ScatterBt2Model();
        $data = $model->getAllForChart();

        return $this->respond([
            'success' => true,
            'message' => 'Data scatter BT2 berhasil diambil',
            'data' => $data
        ]);
    }

    public function scatter_bt3()
    {
        $model = new ScatterBt3Model();
        $data = $model->getAllForChart();

        return $this->respond([
            'success' => true,
            'message' => 'Data scatter BT3 berhasil diambil',
            'data' => $data
        ]);
    }

    public function scatter_bt4()
    {
        $model = new ScatterBt4Model();
        $data = $model->getAllForChart();

        return $this->respond([
            'success' => true,
            'message' => 'Data scatter BT4 berhasil diambil',
            'data' => $data
        ]);
    }

    public function scatter_bt6()
    {
        $model = new ScatterBt6Model();
        $data = $model->getAllForChart();

        return $this->respond([
            'success' => true,
            'message' => 'Data scatter BT6 berhasil diambil',
            'data' => $data
        ]);
    }

    public function scatter_bt7()
    {
        $model = new ScatterBt7Model();
        $data = $model->getAllForChart();

        return $this->respond([
            'success' => true,
            'message' => 'Data scatter BT7 berhasil diambil',
            'data' => $data
        ]);
    }

    public function scatter_bt8()
    {
        $model = new ScatterBt8Model();
        $data = $model->getAllForChart();

        return $this->respond([
            'success' => true,
            'message' => 'Data scatter BT8 berhasil diambil',
            'data' => $data
        ]);
    }

    // === ALL DATA BTM ===
    public function all_data()
    {
        $pengukuranModel = new PengukuranBtmModel();
        
        // Bacaan
        $bacaanBt1Model = new BacaanBt1Model();
        $bacaanBt2Model = new BacaanBt2Model();
        $bacaanBt3Model = new BacaanBt3Model();
        $bacaanBt4Model = new BacaanBt4Model();
        $bacaanBt5Model = new BacaanBt5Model();
        $bacaanBt6Model = new BacaanBt6Model();
        $bacaanBt7Model = new BacaanBt7Model();
        $bacaanBt8Model = new BacaanBt8Model();
        
        // Perhitungan
        $perhitunganBt1Model = new PerhitunganBt1Model();
        $perhitunganBt2Model = new PerhitunganBt2Model();
        $perhitunganBt3Model = new PerhitunganBt3Model();
        $perhitunganBt4Model = new PerhitunganBt4Model();
        $perhitunganBt5Model = new PerhitunganBt5Model();
        $perhitunganBt6Model = new PerhitunganBt6Model();
        $perhitunganBt7Model = new PerhitunganBt7Model();
        $perhitunganBt8Model = new PerhitunganBt8Model();
        
        // Scatter
        $scatterBt1Model = new ScatterBt1Model();
        $scatterBt2Model = new ScatterBt2Model();
        $scatterBt3Model = new ScatterBt3Model();
        $scatterBt4Model = new ScatterBt4Model();
        $scatterBt6Model = new ScatterBt6Model();
        $scatterBt7Model = new ScatterBt7Model();
        $scatterBt8Model = new ScatterBt8Model();

        $data = [
            'pengukuran' => $pengukuranModel->getAllPengukuran(),
            
            // Bacaan
            'bacaan_bt1' => $bacaanBt1Model->getAllBacaan(),
            'bacaan_bt2' => $bacaanBt2Model->getAllBacaan(),
            'bacaan_bt3' => $bacaanBt3Model->getAllBacaan(),
            'bacaan_bt4' => $bacaanBt4Model->getAllBacaan(),
            'bacaan_bt5' => $bacaanBt5Model->getAllBacaan(),
            'bacaan_bt6' => $bacaanBt6Model->getAllBacaan(),
            'bacaan_bt7' => $bacaanBt7Model->getAllBacaan(),
            'bacaan_bt8' => $bacaanBt8Model->getAllBacaan(),
            
            // Perhitungan
            'perhitungan_bt1' => $perhitunganBt1Model->getAllPerhitunganBt1(),
            'perhitungan_bt2' => $perhitunganBt2Model->getAllPerhitunganBt2(),
            'perhitungan_bt3' => $perhitunganBt3Model->getAllPerhitunganBt3(),
            'perhitungan_bt4' => $perhitunganBt4Model->getAllPerhitunganBt4(),
            'perhitungan_bt5' => $perhitunganBt5Model->getAllPerhitunganBt5(),
            'perhitungan_bt6' => $perhitunganBt6Model->getAllPerhitunganBt6(),
            'perhitungan_bt7' => $perhitunganBt7Model->getAllPerhitunganBt7(),
            'perhitungan_bt8' => $perhitunganBt8Model->getAllPerhitunganBt8(),
            
            // Scatter
            'scatter_bt1' => $scatterBt1Model->getAllForChart(),
            'scatter_bt2' => $scatterBt2Model->getAllForChart(),
            'scatter_bt3' => $scatterBt3Model->getAllForChart(),
            'scatter_bt4' => $scatterBt4Model->getAllForChart(),
            'scatter_bt6' => $scatterBt6Model->getAllForChart(),
            'scatter_bt7' => $scatterBt7Model->getAllForChart(),
            'scatter_bt8' => $scatterBt8Model->getAllForChart()
        ];

        return $this->respond([
            'success' => true,
            'message' => 'Semua data BTM berhasil diambil',
            'data' => $data
        ]);
    }

    // === DATA BY PENGUKURAN ID ===
    public function by_pengukuran($id_pengukuran)
    {
        $pengukuranModel = new PengukuranBtmModel();
        
        // Bacaan
        $bacaanBt1Model = new BacaanBt1Model();
        $bacaanBt2Model = new BacaanBt2Model();
        $bacaanBt3Model = new BacaanBt3Model();
        $bacaanBt4Model = new BacaanBt4Model();
        $bacaanBt5Model = new BacaanBt5Model();
        $bacaanBt6Model = new BacaanBt6Model();
        $bacaanBt7Model = new BacaanBt7Model();
        $bacaanBt8Model = new BacaanBt8Model();
        
        // Perhitungan
        $perhitunganBt1Model = new PerhitunganBt1Model();
        $perhitunganBt2Model = new PerhitunganBt2Model();
        $perhitunganBt3Model = new PerhitunganBt3Model();
        $perhitunganBt4Model = new PerhitunganBt4Model();
        $perhitunganBt5Model = new PerhitunganBt5Model();
        $perhitunganBt6Model = new PerhitunganBt6Model();
        $perhitunganBt7Model = new PerhitunganBt7Model();
        $perhitunganBt8Model = new PerhitunganBt8Model();
        
        // Scatter
        $scatterBt1Model = new ScatterBt1Model();
        $scatterBt2Model = new ScatterBt2Model();
        $scatterBt3Model = new ScatterBt3Model();
        $scatterBt4Model = new ScatterBt4Model();
        $scatterBt6Model = new ScatterBt6Model();
        $scatterBt7Model = new ScatterBt7Model();
        $scatterBt8Model = new ScatterBt8Model();

        $data = [
            'pengukuran' => $pengukuranModel->find($id_pengukuran),
            
            // Bacaan
            'bacaan_bt1' => $bacaanBt1Model->getByPengukuran($id_pengukuran),
            'bacaan_bt2' => $bacaanBt2Model->getByPengukuran($id_pengukuran),
            'bacaan_bt3' => $bacaanBt3Model->getByPengukuran($id_pengukuran),
            'bacaan_bt4' => $bacaanBt4Model->getByPengukuran($id_pengukuran),
            'bacaan_bt5' => $bacaanBt5Model->getByPengukuran($id_pengukuran),
            'bacaan_bt6' => $bacaanBt6Model->getByPengukuran($id_pengukuran),
            'bacaan_bt7' => $bacaanBt7Model->getByPengukuran($id_pengukuran),
            'bacaan_bt8' => $bacaanBt8Model->getByPengukuran($id_pengukuran),
            
            // Perhitungan
            'perhitungan_bt1' => $perhitunganBt1Model->getByPengukuran($id_pengukuran),
            'perhitungan_bt2' => $perhitunganBt2Model->getByPengukuran($id_pengukuran),
            'perhitungan_bt3' => $perhitunganBt3Model->getByPengukuran($id_pengukuran),
            'perhitungan_bt4' => $perhitunganBt4Model->getByPengukuran($id_pengukuran),
            'perhitungan_bt5' => $perhitunganBt5Model->getByPengukuran($id_pengukuran),
            'perhitungan_bt6' => $perhitunganBt6Model->getByPengukuran($id_pengukuran),
            'perhitungan_bt7' => $perhitunganBt7Model->getByPengukuran($id_pengukuran),
            'perhitungan_bt8' => $perhitunganBt8Model->getByPengukuran($id_pengukuran),
            
            // Scatter
            'scatter_bt1' => $scatterBt1Model->getByPengukuran($id_pengukuran),
            'scatter_bt2' => $scatterBt2Model->getByPengukuran($id_pengukuran),
            'scatter_bt3' => $scatterBt3Model->getByPengukuran($id_pengukuran),
            'scatter_bt4' => $scatterBt4Model->getByPengukuran($id_pengukuran),
            'scatter_bt6' => $scatterBt6Model->getByPengukuran($id_pengukuran),
            'scatter_bt7' => $scatterBt7Model->getByPengukuran($id_pengukuran),
            'scatter_bt8' => $scatterBt8Model->getByPengukuran($id_pengukuran)
        ];

        return $this->respond([
            'success' => true,
            'message' => 'Data BTM untuk pengukuran ID ' . $id_pengukuran . ' berhasil diambil',
            'data' => $data
        ]);
    }

    // === SYNC DATA BTM ===
    public function sync()
    {
        $lastSync = $this->request->getGet('last_sync');
        
        $pengukuranModel = new PengukuranBtmModel();
        
        // Bacaan
        $bacaanBt1Model = new BacaanBt1Model();
        $bacaanBt2Model = new BacaanBt2Model();
        $bacaanBt3Model = new BacaanBt3Model();
        $bacaanBt4Model = new BacaanBt4Model();
        $bacaanBt5Model = new BacaanBt5Model();
        $bacaanBt6Model = new BacaanBt6Model();
        $bacaanBt7Model = new BacaanBt7Model();
        $bacaanBt8Model = new BacaanBt8Model();
        
        // Perhitungan
        $perhitunganBt1Model = new PerhitunganBt1Model();
        $perhitunganBt2Model = new PerhitunganBt2Model();
        $perhitunganBt3Model = new PerhitunganBt3Model();
        $perhitunganBt4Model = new PerhitunganBt4Model();
        $perhitunganBt5Model = new PerhitunganBt5Model();
        $perhitunganBt6Model = new PerhitunganBt6Model();
        $perhitunganBt7Model = new PerhitunganBt7Model();
        $perhitunganBt8Model = new PerhitunganBt8Model();
        
        // Scatter
        $scatterBt1Model = new ScatterBt1Model();
        $scatterBt2Model = new ScatterBt2Model();
        $scatterBt3Model = new ScatterBt3Model();
        $scatterBt4Model = new ScatterBt4Model();
        $scatterBt6Model = new ScatterBt6Model();
        $scatterBt7Model = new ScatterBt7Model();
        $scatterBt8Model = new ScatterBt8Model();

        $syncData = [];

        if ($lastSync) {
            $syncData = [
                'pengukuran' => $pengukuranModel->where('updated_at >=', $lastSync)->findAll(),
                
                // Bacaan
                'bacaan_bt1' => $bacaanBt1Model->where('updated_at >=', $lastSync)->findAll(),
                'bacaan_bt2' => $bacaanBt2Model->where('updated_at >=', $lastSync)->findAll(),
                'bacaan_bt3' => $bacaanBt3Model->where('updated_at >=', $lastSync)->findAll(),
                'bacaan_bt4' => $bacaanBt4Model->where('updated_at >=', $lastSync)->findAll(),
                'bacaan_bt5' => $bacaanBt5Model->where('updated_at >=', $lastSync)->findAll(),
                'bacaan_bt6' => $bacaanBt6Model->where('updated_at >=', $lastSync)->findAll(),
                'bacaan_bt7' => $bacaanBt7Model->where('updated_at >=', $lastSync)->findAll(),
                'bacaan_bt8' => $bacaanBt8Model->where('updated_at >=', $lastSync)->findAll(),
                
                // Perhitungan
                'perhitungan_bt1' => $perhitunganBt1Model->where('updated_at >=', $lastSync)->findAll(),
                'perhitungan_bt2' => $perhitunganBt2Model->where('updated_at >=', $lastSync)->findAll(),
                'perhitungan_bt3' => $perhitunganBt3Model->where('updated_at >=', $lastSync)->findAll(),
                'perhitungan_bt4' => $perhitunganBt4Model->where('updated_at >=', $lastSync)->findAll(),
                'perhitungan_bt5' => $perhitunganBt5Model->where('updated_at >=', $lastSync)->findAll(),
                'perhitungan_bt6' => $perhitunganBt6Model->where('updated_at >=', $lastSync)->findAll(),
                'perhitungan_bt7' => $perhitunganBt7Model->where('updated_at >=', $lastSync)->findAll(),
                'perhitungan_bt8' => $perhitunganBt8Model->where('updated_at >=', $lastSync)->findAll(),
                
                // Scatter
                'scatter_bt1' => $scatterBt1Model->where('updated_at >=', $lastSync)->findAll(),
                'scatter_bt2' => $scatterBt2Model->where('updated_at >=', $lastSync)->findAll(),
                'scatter_bt3' => $scatterBt3Model->where('updated_at >=', $lastSync)->findAll(),
                'scatter_bt4' => $scatterBt4Model->where('updated_at >=', $lastSync)->findAll(),
                'scatter_bt6' => $scatterBt6Model->where('updated_at >=', $lastSync)->findAll(),
                'scatter_bt7' => $scatterBt7Model->where('updated_at >=', $lastSync)->findAll(),
                'scatter_bt8' => $scatterBt8Model->where('updated_at >=', $lastSync)->findAll()
            ];
        } else {
            $syncData = [
                'pengukuran' => $pengukuranModel->getAllPengukuran(),
                
                // Bacaan
                'bacaan_bt1' => $bacaanBt1Model->getAllBacaan(),
                'bacaan_bt2' => $bacaanBt2Model->getAllBacaan(),
                'bacaan_bt3' => $bacaanBt3Model->getAllBacaan(),
                'bacaan_bt4' => $bacaanBt4Model->getAllBacaan(),
                'bacaan_bt5' => $bacaanBt5Model->getAllBacaan(),
                'bacaan_bt6' => $bacaanBt6Model->getAllBacaan(),
                'bacaan_bt7' => $bacaanBt7Model->getAllBacaan(),
                'bacaan_bt8' => $bacaanBt8Model->getAllBacaan(),
                
                // Perhitungan
                'perhitungan_bt1' => $perhitunganBt1Model->getAllPerhitunganBt1(),
                'perhitungan_bt2' => $perhitunganBt2Model->getAllPerhitunganBt2(),
                'perhitungan_bt3' => $perhitunganBt3Model->getAllPerhitunganBt3(),
                'perhitungan_bt4' => $perhitunganBt4Model->getAllPerhitunganBt4(),
                'perhitungan_bt5' => $perhitunganBt5Model->getAllPerhitunganBt5(),
                'perhitungan_bt6' => $perhitunganBt6Model->getAllPerhitunganBt6(),
                'perhitungan_bt7' => $perhitunganBt7Model->getAllPerhitunganBt7(),
                'perhitungan_bt8' => $perhitunganBt8Model->getAllPerhitunganBt8(),
                
                // Scatter
                'scatter_bt1' => $scatterBt1Model->getAllForChart(),
                'scatter_bt2' => $scatterBt2Model->getAllForChart(),
                'scatter_bt3' => $scatterBt3Model->getAllForChart(),
                'scatter_bt4' => $scatterBt4Model->getAllForChart(),
                'scatter_bt6' => $scatterBt6Model->getAllForChart(),
                'scatter_bt7' => $scatterBt7Model->getAllForChart(),
                'scatter_bt8' => $scatterBt8Model->getAllForChart()
            ];
        }

        return $this->respond([
            'success' => true,
            'message' => 'Data sync BTM berhasil diambil',
            'last_sync' => date('Y-m-d H:i:s'),
            'data' => $syncData
        ]);
    }
}