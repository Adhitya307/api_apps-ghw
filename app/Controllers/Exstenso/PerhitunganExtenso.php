<?php

namespace App\Controllers\Exstenso;

use App\Controllers\BaseController;
use App\Models\Exstenso\DeformasiEx1Model;
use App\Models\Exstenso\PembacaanEx1Model;
use App\Models\Exstenso\DeformasiEx2Model;
use App\Models\Exstenso\PembacaanEx2Model;
use App\Models\Exstenso\DeformasiEx3Model;
use App\Models\Exstenso\PembacaanEx3Model;
use App\Models\Exstenso\DeformasiEx4Model;
use App\Models\Exstenso\PembacaanEx4Model;

class PerhitunganExtenso extends BaseController
{
    protected $deformasiEx1Model;
    protected $pembacaanEx1Model;
    protected $deformasiEx2Model;
    protected $pembacaanEx2Model;
    protected $deformasiEx3Model;
    protected $pembacaanEx3Model;
    protected $deformasiEx4Model;
    protected $pembacaanEx4Model;

    public function __construct()
    {
        $this->deformasiEx1Model = new DeformasiEx1Model();
        $this->pembacaanEx1Model = new PembacaanEx1Model();
        $this->deformasiEx2Model = new DeformasiEx2Model();
        $this->pembacaanEx2Model = new PembacaanEx2Model();
        $this->deformasiEx3Model = new DeformasiEx3Model();
        $this->pembacaanEx3Model = new PembacaanEx3Model();
        $this->deformasiEx4Model = new DeformasiEx4Model();
        $this->pembacaanEx4Model = new PembacaanEx4Model();
    }

    public function HitungDeformasiEx1()
    {
        // Ambil data dari input JSON
        $data = $this->request->getJSON(true);

        // Validasi data
        if (!$data || !isset($data['id_pengukuran'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data id_pengukuran diperlukan'
            ]);
        }

        $idPengukuran = $data['id_pengukuran'];

        // CEK APAKAH SUDAH ADA DATA DEFORMASI
        $existingDeformasi = $this->deformasiEx1Model->where('id_pengukuran', $idPengukuran)->first();
        if ($existingDeformasi) {
            return $this->response->setJSON([
                'status' => 'info',
                'message' => 'Data deformasi Ex1 untuk pengukuran ini sudah ada',
                'data' => $existingDeformasi
            ]);
        }

        // Ambil data dari tabel pembacaan Ex1
        $pembacaan = $this->pembacaanEx1Model->where('id_pengukuran', $idPengukuran)->first();

        if (!$pembacaan) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data pembacaan Ex1 tidak ditemukan untuk id_pengukuran: ' . $idPengukuran
            ]);
        }

        // Nilai default pemb_awal untuk Ex1
        $pemb_awal10 = 35.00;
        $pemb_awal20 = 40.95;
        $pemb_awal30 = 29.80;

        // Data untuk disimpan - GUNAKAN DATA DARI TABEL PEMBACAAN
        $hasil = [
            'id_pengukuran' => $idPengukuran,
            'pemb_awal10'   => $pemb_awal10,
            'pemb_awal20'   => $pemb_awal20,
            'pemb_awal30'   => $pemb_awal30,
            'deformasi_10'  => $pembacaan['pembacaan_10'] - $pemb_awal10,
            'deformasi_20'  => $pembacaan['pembacaan_20'] - $pemb_awal20,
            'deformasi_30'  => $pembacaan['pembacaan_30'] - $pemb_awal30,
        ];

        // Simpan ke database
        $insertId = $this->deformasiEx1Model->insert($hasil);

        // Response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Perhitungan deformasi Ex1 berhasil',
            'data' => $this->deformasiEx1Model->find($insertId)
        ]);
    }

    public function HitungDeformasiEx2()
    {
        // Ambil data dari input JSON
        $data = $this->request->getJSON(true);

        // Validasi data
        if (!$data || !isset($data['id_pengukuran'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data id_pengukuran diperlukan'
            ]);
        }

        $idPengukuran = $data['id_pengukuran'];

        // CEK APAKAH SUDAH ADA DATA DEFORMASI
        $existingDeformasi = $this->deformasiEx2Model->where('id_pengukuran', $idPengukuran)->first();
        if ($existingDeformasi) {
            return $this->response->setJSON([
                'status' => 'info',
                'message' => 'Data deformasi Ex2 untuk pengukuran ini sudah ada',
                'data' => $existingDeformasi
            ]);
        }

        // Ambil data dari tabel pembacaan Ex2
        $pembacaan = $this->pembacaanEx2Model->where('id_pengukuran', $idPengukuran)->first();

        if (!$pembacaan) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data pembacaan Ex2 tidak ditemukan untuk id_pengukuran: ' . $idPengukuran
            ]);
        }

        // Nilai default pemb_awal untuk Ex2
        $pemb_awal10 = 22.60;
        $pemb_awal20 = 23.70;
        $pemb_awal30 = 30.75;

        // Data untuk disimpan - GUNAKAN DATA DARI TABEL PEMBACAAN
        $hasil = [
            'id_pengukuran' => $idPengukuran,
            'pemb_awal10'   => $pemb_awal10,
            'pemb_awal20'   => $pemb_awal20,
            'pemb_awal30'   => $pemb_awal30,
            'deformasi_10'  => $pembacaan['pembacaan_10'] - $pemb_awal10,
            'deformasi_20'  => $pembacaan['pembacaan_20'] - $pemb_awal20,
            'deformasi_30'  => $pembacaan['pembacaan_30'] - $pemb_awal30,
        ];

        // Simpan ke database
        $insertId = $this->deformasiEx2Model->insert($hasil);

        // Response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Perhitungan deformasi Ex2 berhasil',
            'data' => $this->deformasiEx2Model->find($insertId)
        ]);
    }

    public function HitungDeformasiEx3()
    {
        // Ambil data dari input JSON
        $data = $this->request->getJSON(true);

        // Validasi data
        if (!$data || !isset($data['id_pengukuran'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data id_pengukuran diperlukan'
            ]);
        }

        $idPengukuran = $data['id_pengukuran'];

        // CEK APAKAH SUDAH ADA DATA DEFORMASI
        $existingDeformasi = $this->deformasiEx3Model->where('id_pengukuran', $idPengukuran)->first();
        if ($existingDeformasi) {
            return $this->response->setJSON([
                'status' => 'info',
                'message' => 'Data deformasi Ex3 untuk pengukuran ini sudah ada',
                'data' => $existingDeformasi
            ]);
        }

        // Ambil data dari tabel pembacaan Ex3
        $pembacaan = $this->pembacaanEx3Model->where('id_pengukuran', $idPengukuran)->first();

        if (!$pembacaan) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data pembacaan Ex3 tidak ditemukan untuk id_pengukuran: ' . $idPengukuran
            ]);
        }

        // Nilai default pemb_awal untuk Ex3
        $pemb_awal10 = 37.75;
        $pemb_awal20 = 39.15;
        $pemb_awal30 = 41.40;

        // Data untuk disimpan - GUNAKAN DATA DARI TABEL PEMBACAAN
        $hasil = [
            'id_pengukuran' => $idPengukuran,
            'pemb_awal10'   => $pemb_awal10,
            'pemb_awal20'   => $pemb_awal20,
            'pemb_awal30'   => $pemb_awal30,
            'deformasi_10'  => $pembacaan['pembacaan_10'] - $pemb_awal10,
            'deformasi_20'  => $pembacaan['pembacaan_20'] - $pemb_awal20,
            'deformasi_30'  => $pembacaan['pembacaan_30'] - $pemb_awal30,
        ];

        // Simpan ke database
        $insertId = $this->deformasiEx3Model->insert($hasil);

        // Response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Perhitungan deformasi Ex3 berhasil',
            'data' => $this->deformasiEx3Model->find($insertId)
        ]);
    }

    public function HitungDeformasiEx4()
    {
        // Ambil data dari input JSON
        $data = $this->request->getJSON(true);

        // Validasi data
        if (!$data || !isset($data['id_pengukuran'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data id_pengukuran diperlukan'
            ]);
        }

        $idPengukuran = $data['id_pengukuran'];

        // CEK APAKAH SUDAH ADA DATA DEFORMASI
        $existingDeformasi = $this->deformasiEx4Model->where('id_pengukuran', $idPengukuran)->first();
        if ($existingDeformasi) {
            return $this->response->setJSON([
                'status' => 'info',
                'message' => 'Data deformasi Ex4 untuk pengukuran ini sudah ada',
                'data' => $existingDeformasi
            ]);
        }

        // Ambil data dari tabel pembacaan Ex4
        $pembacaan = $this->pembacaanEx4Model->where('id_pengukuran', $idPengukuran)->first();

        if (!$pembacaan) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data pembacaan Ex4 tidak ditemukan untuk id_pengukuran: ' . $idPengukuran
            ]);
        }

        // Nilai default pemb_awal untuk Ex4
        $pemb_awal10 = 33.80;
        $pemb_awal20 = 29.30;
        $pemb_awal30 = 48.95;

        // Data untuk disimpan - GUNAKAN DATA DARI TABEL PEMBACAAN
        $hasil = [
            'id_pengukuran' => $idPengukuran,
            'pemb_awal10'   => $pemb_awal10,
            'pemb_awal20'   => $pemb_awal20,
            'pemb_awal30'   => $pemb_awal30,
            'deformasi_10'  => $pembacaan['pembacaan_10'] - $pemb_awal10,
            'deformasi_20'  => $pembacaan['pembacaan_20'] - $pemb_awal20,
            'deformasi_30'  => $pembacaan['pembacaan_30'] - $pemb_awal30,
        ];

        // Simpan ke database
        $insertId = $this->deformasiEx4Model->insert($hasil);

        // Response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Perhitungan deformasi Ex4 berhasil',
            'data' => $this->deformasiEx4Model->find($insertId)
        ]);
    }

    /**
     * Hitung semua deformasi sekaligus (Ex1, Ex2, Ex3, dan Ex4)
     */
    public function HitungSemuaDeformasi()
    {
        // Ambil data dari input JSON
        $data = $this->request->getJSON(true);

        // Validasi data
        if (!$data || !isset($data['id_pengukuran'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data id_pengukuran diperlukan'
            ]);
        }

        $idPengukuran = $data['id_pengukuran'];
        $results = [];

        // Hitung Deformasi Ex1
        $pembacaanEx1 = $this->pembacaanEx1Model->where('id_pengukuran', $idPengukuran)->first();
        if ($pembacaanEx1) {
            // CEK APAKAH SUDAH ADA DATA DEFORMASI Ex1
            $existingEx1 = $this->deformasiEx1Model->where('id_pengukuran', $idPengukuran)->first();
            if (!$existingEx1) {
                $pemb_awal10 = 35.00;
                $pemb_awal20 = 40.95;
                $pemb_awal30 = 29.80;

                $hasilEx1 = [
                    'id_pengukuran' => $idPengukuran,
                    'pemb_awal10'   => $pemb_awal10,
                    'pemb_awal20'   => $pemb_awal20,
                    'pemb_awal30'   => $pemb_awal30,
                    'deformasi_10'  => $pembacaanEx1['pembacaan_10'] - $pemb_awal10,
                    'deformasi_20'  => $pembacaanEx1['pembacaan_20'] - $pemb_awal20,
                    'deformasi_30'  => $pembacaanEx1['pembacaan_30'] - $pemb_awal30,
                ];

                $insertIdEx1 = $this->deformasiEx1Model->insert($hasilEx1);
                $results['ex1'] = $this->deformasiEx1Model->find($insertIdEx1);
            } else {
                $results['ex1'] = $existingEx1;
            }
        }

        // Hitung Deformasi Ex2
        $pembacaanEx2 = $this->pembacaanEx2Model->where('id_pengukuran', $idPengukuran)->first();
        if ($pembacaanEx2) {
            // CEK APAKAH SUDAH ADA DATA DEFORMASI Ex2
            $existingEx2 = $this->deformasiEx2Model->where('id_pengukuran', $idPengukuran)->first();
            if (!$existingEx2) {
                $pemb_awal10 = 22.60;
                $pemb_awal20 = 23.70;
                $pemb_awal30 = 30.75;

                $hasilEx2 = [
                    'id_pengukuran' => $idPengukuran,
                    'pemb_awal10'   => $pemb_awal10,
                    'pemb_awal20'   => $pemb_awal20,
                    'pemb_awal30'   => $pemb_awal30,
                    'deformasi_10'  => $pembacaanEx2['pembacaan_10'] - $pemb_awal10,
                    'deformasi_20'  => $pembacaanEx2['pembacaan_20'] - $pemb_awal20,
                    'deformasi_30'  => $pembacaanEx2['pembacaan_30'] - $pemb_awal30,
                ];

                $insertIdEx2 = $this->deformasiEx2Model->insert($hasilEx2);
                $results['ex2'] = $this->deformasiEx2Model->find($insertIdEx2);
            } else {
                $results['ex2'] = $existingEx2;
            }
        }

        // Hitung Deformasi Ex3
        $pembacaanEx3 = $this->pembacaanEx3Model->where('id_pengukuran', $idPengukuran)->first();
        if ($pembacaanEx3) {
            // CEK APAKAH SUDAH ADA DATA DEFORMASI Ex3
            $existingEx3 = $this->deformasiEx3Model->where('id_pengukuran', $idPengukuran)->first();
            if (!$existingEx3) {
                $pemb_awal10 = 37.75;
                $pemb_awal20 = 39.15;
                $pemb_awal30 = 41.40;

                $hasilEx3 = [
                    'id_pengukuran' => $idPengukuran,
                    'pemb_awal10'   => $pemb_awal10,
                    'pemb_awal20'   => $pemb_awal20,
                    'pemb_awal30'   => $pemb_awal30,
                    'deformasi_10'  => $pembacaanEx3['pembacaan_10'] - $pemb_awal10,
                    'deformasi_20'  => $pembacaanEx3['pembacaan_20'] - $pemb_awal20,
                    'deformasi_30'  => $pembacaanEx3['pembacaan_30'] - $pemb_awal30,
                ];

                $insertIdEx3 = $this->deformasiEx3Model->insert($hasilEx3);
                $results['ex3'] = $this->deformasiEx3Model->find($insertIdEx3);
            } else {
                $results['ex3'] = $existingEx3;
            }
        }

        // Hitung Deformasi Ex4
        $pembacaanEx4 = $this->pembacaanEx4Model->where('id_pengukuran', $idPengukuran)->first();
        if ($pembacaanEx4) {
            // CEK APAKAH SUDAH ADA DATA DEFORMASI Ex4
            $existingEx4 = $this->deformasiEx4Model->where('id_pengukuran', $idPengukuran)->first();
            if (!$existingEx4) {
                $pemb_awal10 = 33.80;
                $pemb_awal20 = 29.30;
                $pemb_awal30 = 48.95;

                $hasilEx4 = [
                    'id_pengukuran' => $idPengukuran,
                    'pemb_awal10'   => $pemb_awal10,
                    'pemb_awal20'   => $pemb_awal20,
                    'pemb_awal30'   => $pemb_awal30,
                    'deformasi_10'  => $pembacaanEx4['pembacaan_10'] - $pemb_awal10,
                    'deformasi_20'  => $pembacaanEx4['pembacaan_20'] - $pemb_awal20,
                    'deformasi_30'  => $pembacaanEx4['pembacaan_30'] - $pemb_awal30,
                ];

                $insertIdEx4 = $this->deformasiEx4Model->insert($hasilEx4);
                $results['ex4'] = $this->deformasiEx4Model->find($insertIdEx4);
            } else {
                $results['ex4'] = $existingEx4;
            }
        }

        // Response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Perhitungan semua deformasi berhasil',
            'data' => $results
        ]);
    }
}