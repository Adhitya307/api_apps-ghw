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

    /**
     * Helper function untuk menghitung deformasi dengan logika yang benar
     * JIKA PEMBACAAN 0 ATAU NULL → JANGAN HITUNG DEFORMASI
     */
    private function hitungDeformasiSingle($idPengukuran, $pembacaanModel, $deformasiModel, $pembAwal10, $pembAwal20, $pembAwal30)
    {
        // Ambil data dari tabel pembacaan
        $pembacaan = $pembacaanModel->where('id_pengukuran', $idPengukuran)->first();

        if (!$pembacaan) {
            return [
                'status' => 'error',
                'message' => 'Data pembacaan tidak ditemukan'
            ];
        }

        // Cek apakah sudah ada data deformasi
        $existingDeformasi = $deformasiModel->where('id_pengukuran', $idPengukuran)->first();

        // Data untuk disimpan
        $data = [
            'id_pengukuran' => $idPengukuran,
            'pemb_awal10'   => $pembAwal10,
            'pemb_awal20'   => $pembAwal20,
            'pemb_awal30'   => $pembAwal30
        ];

        // LOGIKA BARU: Hitung deformasi hanya jika pembacaan tidak null dan tidak 0
        // Jika 0 atau null, pertahankan nilai yang sudah ada atau set ke 0
        
        // Untuk deformasi_10
        if (isset($pembacaan['pembacaan_10']) && $pembacaan['pembacaan_10'] !== null && $pembacaan['pembacaan_10'] != 0) {
            $data['deformasi_10'] = $pembacaan['pembacaan_10'] - $pembAwal10;
        } else if ($existingDeformasi && isset($existingDeformasi['deformasi_10'])) {
            $data['deformasi_10'] = $existingDeformasi['deformasi_10']; // Pertahankan nilai lama
        } else {
            $data['deformasi_10'] = 0; // Nilai default jika belum ada data
        }

        // Untuk deformasi_20
        if (isset($pembacaan['pembacaan_20']) && $pembacaan['pembacaan_20'] !== null && $pembacaan['pembacaan_20'] != 0) {
            $data['deformasi_20'] = $pembacaan['pembacaan_20'] - $pembAwal20;
        } else if ($existingDeformasi && isset($existingDeformasi['deformasi_20'])) {
            $data['deformasi_20'] = $existingDeformasi['deformasi_20']; // Pertahankan nilai lama
        } else {
            $data['deformasi_20'] = 0; // Nilai default jika belum ada data
        }

        // Untuk deformasi_30
        if (isset($pembacaan['pembacaan_30']) && $pembacaan['pembacaan_30'] !== null && $pembacaan['pembacaan_30'] != 0) {
            $data['deformasi_30'] = $pembacaan['pembacaan_30'] - $pembAwal30;
        } else if ($existingDeformasi && isset($existingDeformasi['deformasi_30'])) {
            $data['deformasi_30'] = $existingDeformasi['deformasi_30']; // Pertahankan nilai lama
        } else {
            $data['deformasi_30'] = 0; // Nilai default jika belum ada data
        }

        // Simpan ke database
        if ($existingDeformasi) {
            $deformasiModel->update($existingDeformasi[$deformasiModel->primaryKey], $data);
            $resultData = $deformasiModel->find($existingDeformasi[$deformasiModel->primaryKey]);
            $action = 'updated';
        } else {
            $insertId = $deformasiModel->insert($data);
            $resultData = $deformasiModel->find($insertId);
            $action = 'created';
        }

        return [
            'status' => 'success',
            'action' => $action,
            'data' => $resultData
        ];
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

        // Nilai default pemb_awal untuk Ex1
        $pemb_awal10 = 35.00;
        $pemb_awal20 = 40.95;
        $pemb_awal30 = 29.80;

        $result = $this->hitungDeformasiSingle(
            $idPengukuran,
            $this->pembacaanEx1Model,
            $this->deformasiEx1Model,
            $pemb_awal10,
            $pemb_awal20,
            $pemb_awal30
        );

        if ($result['status'] === 'success') {
            $message = $result['action'] === 'created' 
                ? 'Perhitungan deformasi Ex1 berhasil dibuat' 
                : 'Perhitungan deformasi Ex1 berhasil diperbarui';

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message,
                'data' => $result['data']
            ]);
        } else {
            return $this->response->setJSON($result);
        }
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

        // Nilai default pemb_awal untuk Ex2
        $pemb_awal10 = 22.60;
        $pemb_awal20 = 23.70;
        $pemb_awal30 = 30.75;

        $result = $this->hitungDeformasiSingle(
            $idPengukuran,
            $this->pembacaanEx2Model,
            $this->deformasiEx2Model,
            $pemb_awal10,
            $pemb_awal20,
            $pemb_awal30
        );

        if ($result['status'] === 'success') {
            $message = $result['action'] === 'created' 
                ? 'Perhitungan deformasi Ex2 berhasil dibuat' 
                : 'Perhitungan deformasi Ex2 berhasil diperbarui';

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message,
                'data' => $result['data']
            ]);
        } else {
            return $this->response->setJSON($result);
        }
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

        // Nilai default pemb_awal untuk Ex3
        $pemb_awal10 = 37.75;
        $pemb_awal20 = 39.15;
        $pemb_awal30 = 41.40;

        $result = $this->hitungDeformasiSingle(
            $idPengukuran,
            $this->pembacaanEx3Model,
            $this->deformasiEx3Model,
            $pemb_awal10,
            $pemb_awal20,
            $pemb_awal30
        );

        if ($result['status'] === 'success') {
            $message = $result['action'] === 'created' 
                ? 'Perhitungan deformasi Ex3 berhasil dibuat' 
                : 'Perhitungan deformasi Ex3 berhasil diperbarui';

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message,
                'data' => $result['data']
            ]);
        } else {
            return $this->response->setJSON($result);
        }
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

        // Nilai default pemb_awal untuk Ex4
        $pemb_awal10 = 33.80;
        $pemb_awal20 = 29.30;
        $pemb_awal30 = 48.95;

        $result = $this->hitungDeformasiSingle(
            $idPengukuran,
            $this->pembacaanEx4Model,
            $this->deformasiEx4Model,
            $pemb_awal10,
            $pemb_awal20,
            $pemb_awal30
        );

        if ($result['status'] === 'success') {
            $message = $result['action'] === 'created' 
                ? 'Perhitungan deformasi Ex4 berhasil dibuat' 
                : 'Perhitungan deformasi Ex4 berhasil diperbarui';

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message,
                'data' => $result['data']
            ]);
        } else {
            return $this->response->setJSON($result);
        }
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
        $summary = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0
        ];

        // Hitung Deformasi Ex1
        $resultEx1 = $this->hitungDeformasiSingle(
            $idPengukuran,
            $this->pembacaanEx1Model,
            $this->deformasiEx1Model,
            35.00, 40.95, 29.80
        );
        if ($resultEx1['status'] === 'success') {
            $results['ex1'] = $resultEx1['data'];
            $summary[$resultEx1['action']]++;
        } else {
            $summary['skipped']++;
        }

        // Hitung Deformasi Ex2
        $resultEx2 = $this->hitungDeformasiSingle(
            $idPengukuran,
            $this->pembacaanEx2Model,
            $this->deformasiEx2Model,
            22.60, 23.70, 30.75
        );
        if ($resultEx2['status'] === 'success') {
            $results['ex2'] = $resultEx2['data'];
            $summary[$resultEx2['action']]++;
        } else {
            $summary['skipped']++;
        }

        // Hitung Deformasi Ex3
        $resultEx3 = $this->hitungDeformasiSingle(
            $idPengukuran,
            $this->pembacaanEx3Model,
            $this->deformasiEx3Model,
            37.75, 39.15, 41.40
        );
        if ($resultEx3['status'] === 'success') {
            $results['ex3'] = $resultEx3['data'];
            $summary[$resultEx3['action']]++;
        } else {
            $summary['skipped']++;
        }

        // Hitung Deformasi Ex4
        $resultEx4 = $this->hitungDeformasiSingle(
            $idPengukuran,
            $this->pembacaanEx4Model,
            $this->deformasiEx4Model,
            33.80, 29.30, 48.95
        );
        if ($resultEx4['status'] === 'success') {
            $results['ex4'] = $resultEx4['data'];
            $summary[$resultEx4['action']]++;
        } else {
            $summary['skipped']++;
        }

        // Response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Perhitungan semua deformasi berhasil',
            'summary' => $summary,
            'data' => $results
        ]);
    }

    /**
     * API untuk mendapatkan data deformasi berdasarkan id_pengukuran
     */
    public function GetDeformasiByPengukuran()
    {
        $idPengukuran = $this->request->getGet('id_pengukuran');

        if (!$idPengukuran) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Parameter id_pengukuran diperlukan'
            ]);
        }

        $results = [];

        // Ambil data deformasi Ex1
        $deformasiEx1 = $this->deformasiEx1Model->where('id_pengukuran', $idPengukuran)->first();
        if ($deformasiEx1) {
            $results['ex1'] = $deformasiEx1;
        }

        // Ambil data deformasi Ex2
        $deformasiEx2 = $this->deformasiEx2Model->where('id_pengukuran', $idPengukuran)->first();
        if ($deformasiEx2) {
            $results['ex2'] = $deformasiEx2;
        }

        // Ambil data deformasi Ex3
        $deformasiEx3 = $this->deformasiEx3Model->where('id_pengukuran', $idPengukuran)->first();
        if ($deformasiEx3) {
            $results['ex3'] = $deformasiEx3;
        }

        // Ambil data deformasi Ex4
        $deformasiEx4 = $this->deformasiEx4Model->where('id_pengukuran', $idPengukuran)->first();
        if ($deformasiEx4) {
            $results['ex4'] = $deformasiEx4;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $results
        ]);
    }
}