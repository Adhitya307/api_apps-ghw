<?php

namespace App\Controllers\DomBody;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\DomBody\MPengukuranHdm;
use App\Models\DomBody\MPembacaanElv625;
use App\Models\DomBody\MPembacaanElv600;
use App\Models\DomBody\DepthElv625Model;
use App\Models\DomBody\DepthElv600Model;
use App\Models\DomBody\InitialReadingElv625Model;
use App\Models\DomBody\InitialReadingElv600Model;
use App\Models\DomBody\MPergerakanElv625;
use App\Models\DomBody\MPergerakanElv600;

class DamBodyApi extends BaseController
{
    use ResponseTrait;

    // === Data Pengukuran HDM ===
    public function pengukuran()
    {
        $model = new MPengukuranHdm();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data pengukuran HDM berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Pembacaan HDM ELV 625 ===
    public function pembacaan_625()
    {
        $model = new MPembacaanElv625();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data pembacaan ELV 625 berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Pembacaan HDM ELV 600 ===
    public function pembacaan_600()
    {
        $model = new MPembacaanElv600();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data pembacaan ELV 600 berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Depth ELV 625 ===
    public function depth_625()
    {
        $model = new DepthElv625Model();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data depth ELV 625 berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Depth ELV 600 ===
    public function depth_600()
    {
        $model = new DepthElv600Model();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data depth ELV 600 berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Initial Reading ELV 625 ===
    public function initial_625()
    {
        $model = new InitialReadingElv625Model();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data initial reading ELV 625 berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Initial Reading ELV 600 ===
    public function initial_600()
    {
        $model = new InitialReadingElv600Model();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data initial reading ELV 600 berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Pergerakan ELV 625 ===
    public function pergerakan_625()
    {
        $model = new MPergerakanElv625();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data pergerakan ELV 625 berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Pergerakan ELV 600 ===
    public function pergerakan_600()
    {
        $model = new MPergerakanElv600();
        $data = $model->findAll();

        return $this->respond([
            'status' => 'success',
            'message' => 'Data pergerakan ELV 600 berhasil diambil',
            'data'   => $data
        ]);
    }

    // === All Data (Complete Dataset) ===
    public function all_data()
    {
        $pengukuranModel = new MPengukuranHdm();
        $pembacaan625Model = new MPembacaanElv625();
        $pembacaan600Model = new MPembacaanElv600();
        $depth625Model = new DepthElv625Model();
        $depth600Model = new DepthElv600Model();
        $initial625Model = new InitialReadingElv625Model();
        $initial600Model = new InitialReadingElv600Model();
        $pergerakan625Model = new MPergerakanElv625();
        $pergerakan600Model = new MPergerakanElv600();

        $data = [
            'pengukuran' => $pengukuranModel->findAll(),
            'pembacaan_625' => $pembacaan625Model->findAll(),
            'pembacaan_600' => $pembacaan600Model->findAll(),
            'depth_625' => $depth625Model->findAll(),
            'depth_600' => $depth600Model->findAll(),
            'initial_625' => $initial625Model->findAll(),
            'initial_600' => $initial600Model->findAll(),
            'pergerakan_625' => $pergerakan625Model->findAll(),
            'pergerakan_600' => $pergerakan600Model->findAll()
        ];

        return $this->respond([
            'status' => 'success',
            'message' => 'Semua data Dom Body berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Data by Pengukuran ID ===
    public function by_pengukuran($id_pengukuran)
    {
        $pengukuranModel = new MPengukuranHdm();
        $pembacaan625Model = new MPembacaanElv625();
        $pembacaan600Model = new MPembacaanElv600();
        $depth625Model = new DepthElv625Model();
        $depth600Model = new DepthElv600Model();
        $initial625Model = new InitialReadingElv625Model();
        $initial600Model = new InitialReadingElv600Model();
        $pergerakan625Model = new MPergerakanElv625();
        $pergerakan600Model = new MPergerakanElv600();

        $data = [
            'pengukuran' => $pengukuranModel->find($id_pengukuran),
            'pembacaan_625' => $pembacaan625Model->where('id_pengukuran', $id_pengukuran)->findAll(),
            'pembacaan_600' => $pembacaan600Model->where('id_pengukuran', $id_pengukuran)->findAll(),
            'depth_625' => $depth625Model->where('id_pengukuran', $id_pengukuran)->findAll(),
            'depth_600' => $depth600Model->where('id_pengukuran', $id_pengukuran)->findAll(),
            'initial_625' => $initial625Model->where('id_pengukuran', $id_pengukuran)->findAll(),
            'initial_600' => $initial600Model->where('id_pengukuran', $id_pengukuran)->findAll(),
            'pergerakan_625' => $pergerakan625Model->where('id_pengukuran', $id_pengukuran)->findAll(),
            'pergerakan_600' => $pergerakan600Model->where('id_pengukuran', $id_pengukuran)->findAll()
        ];

        return $this->respond([
            'status' => 'success',
            'message' => 'Data Dom Body untuk pengukuran ID ' . $id_pengukuran . ' berhasil diambil',
            'data'   => $data
        ]);
    }

    // === Sync Data (untuk sinkronisasi dari mobile app) ===
    public function sync()
    {
        $lastSync = $this->request->getGet('last_sync');
        
        $pengukuranModel = new MPengukuranHdm();
        $pembacaan625Model = new MPembacaanElv625();
        $pembacaan600Model = new MPembacaanElv600();
        $depth625Model = new DepthElv625Model();
        $depth600Model = new DepthElv600Model();
        $initial625Model = new InitialReadingElv625Model();
        $initial600Model = new InitialReadingElv600Model();
        $pergerakan625Model = new MPergerakanElv625();
        $pergerakan600Model = new MPergerakanElv600();

        $syncData = [];

        // Jika ada last_sync parameter, hanya ambil data yang diupdate setelah tanggal tersebut
        if ($lastSync) {
            $syncData = [
                'pengukuran' => $pengukuranModel->where('updated_at >=', $lastSync)->findAll(),
                'pembacaan_625' => $pembacaan625Model->where('updated_at >=', $lastSync)->findAll(),
                'pembacaan_600' => $pembacaan600Model->where('updated_at >=', $lastSync)->findAll(),
                'depth_625' => $depth625Model->where('updated_at >=', $lastSync)->findAll(),
                'depth_600' => $depth600Model->where('updated_at >=', $lastSync)->findAll(),
                'initial_625' => $initial625Model->where('updated_at >=', $lastSync)->findAll(),
                'initial_600' => $initial600Model->where('updated_at >=', $lastSync)->findAll(),
                'pergerakan_625' => $pergerakan625Model->where('updated_at >=', $lastSync)->findAll(),
                'pergerakan_600' => $pergerakan600Model->where('updated_at >=', $lastSync)->findAll()
            ];
        } else {
            // Jika tidak ada last_sync, ambil semua data
            $syncData = [
                'pengukuran' => $pengukuranModel->findAll(),
                'pembacaan_625' => $pembacaan625Model->findAll(),
                'pembacaan_600' => $pembacaan600Model->findAll(),
                'depth_625' => $depth625Model->findAll(),
                'depth_600' => $depth600Model->findAll(),
                'initial_625' => $initial625Model->findAll(),
                'initial_600' => $initial600Model->findAll(),
                'pergerakan_625' => $pergerakan625Model->findAll(),
                'pergerakan_600' => $pergerakan600Model->findAll()
            ];
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Data sync Dom Body berhasil diambil',
            'last_sync' => date('Y-m-d H:i:s'),
            'data'   => $syncData
        ]);
    }
}