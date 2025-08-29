<?php

namespace App\Controllers\Rembesan;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Rembesan\MDataPengukuran;
use App\Models\Rembesan\MThomsonWeir;
use App\Models\Rembesan\MSR;
use App\Models\Rembesan\MBocoranBaru;

class BackupApi extends BaseController
{
    use ResponseTrait;

    // === Data Pengukuran ===
    public function pengukuran()
    {
        $model = new MDataPengukuran();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    // === Thomson Weir ===
    public function thomson()
    {
        $model = new MThomsonWeir();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    // === SR ===
    public function sr()
    {
        $model = new MSR();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    // === Bocoran Baru ===
    public function bocoran()
    {
        $model = new MBocoranBaru();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'data'   => $data
        ]);
    }
}
