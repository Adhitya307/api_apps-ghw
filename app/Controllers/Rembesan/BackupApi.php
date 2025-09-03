<?php

namespace App\Controllers\Rembesan;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Rembesan\MDataPengukuran;
use App\Models\Rembesan\MThomsonWeir;
use App\Models\Rembesan\MSR;
use App\Models\Rembesan\MBocoranBaru;
use App\Models\Rembesan\PerhitunganBatasMaksimalModel;
use App\Models\Rembesan\PerhitunganBocoranModel;
use App\Models\Rembesan\PerhitunganIntiGaleryModel;
use App\Models\Rembesan\PerhitunganSpillwayModel;
use App\Models\Rembesan\PerhitunganSRModel;
use App\Models\Rembesan\TebingKananModel;
use App\Models\Rembesan\PerhitunganThomsonModel;
use App\Models\Rembesan\TotalBocoranModel;

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
            'message' => 'Data pengukuran berhasil diambil',
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
            'message' => 'Data thomson weir berhasil diambil',
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
            'message' => 'Data SR berhasil diambil',
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
            'message' => 'Data bocoran baru berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Batas Maksimal (Hasil Perhitungan) ===
    public function p_batasmaksimal()
    {
        $model = new PerhitunganBatasMaksimalModel();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data batas maksimal berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Bocoran Baru (Hasil Perhitungan) ===
    public function p_bocoran_baru()
    {
        $model = new PerhitunganBocoranModel();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data bocoran baru (hasil) berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Inti Gallery (Hasil Perhitungan) ===
    public function p_intigalery()
    {
        $model = new PerhitunganIntiGaleryModel();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data inti gallery berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Spillway (Hasil Perhitungan) ===
    public function p_spillway()
    {
        $model = new PerhitunganSpillwayModel();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data spillway berhasil diambil',
            'data'   => $data
        ]);
    }

    // === SR (Hasil Perhitungan) ===
    public function p_sr()
    {
        $model = new PerhitunganSRModel();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data SR (hasil) berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Tebing Kanan (Hasil Perhitungan) ===
    public function p_tebingkanan()
    {
        $model = new TebingKananModel();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data tebing kanan berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Thomson Weir (Hasil Perhitungan) ===
    public function p_thomson_weir()
    {
        $model = new PerhitunganThomsonModel();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data thomson weir (hasil) berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Total Bocoran (Hasil Perhitungan) ===
    public function p_totalbocoran()
    {
        $model = new TotalBocoranModel();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data total bocoran berhasil diambil',
            'data'   => $data
        ]);
    }
}