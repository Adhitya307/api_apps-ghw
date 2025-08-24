<?php

namespace App\Controllers\Rembesan;

use App\Controllers\BaseController;
use App\Models\Rembesan\MBocoranBaru;
use App\Models\Rembesan\PerhitunganBocoranModel;

class BocoranBaruController extends BaseController
{
    protected $bocoranModel;
    protected $perhitunganModel;

    public function __construct()
    {
        $this->bocoranModel = new MBocoranBaru();
        $this->perhitunganModel = new PerhitunganBocoranModel();
    }

    /**
     * Simpan data bocoran baru dan hitung perhitungan
     */
    public function simpanBocoran()
    {
        $data = $this->request->getPost();

        // Validasi data mentah bocoran
        if (!$this->bocoranModel->save($data)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menyimpan bocoran',
                'errors' => $this->bocoranModel->errors()
            ]);
        }

        $pengukuran_id = $this->bocoranModel->getInsertID();

        // Trigger perhitungan otomatis
        $this->hitungLangsung($data['pengukuran_id']);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data bocoran berhasil disimpan dan perhitungan dipicu',
            'pengukuran_id' => $data['pengukuran_id']
        ]);
    }

    /**
     * Fungsi hitung langsung data bocoran untuk satu pengukuran_id
     */
    public function hitungLangsung($pengukuran_id)
    {
        $bocoran = $this->bocoranModel->where('pengukuran_id', $pengukuran_id)->first();

        if (!$bocoran) {
            return false; // Data tidak ditemukan
        }

        // Hitung nilai Q sesuai kode masing-masing
        $talang1 = perhitunganQ_bocoran($bocoran['elv_624_t1'], $bocoran['elv_624_t1_kode']);
        $talang2 = perhitunganQ_bocoran($bocoran['elv_615_t2'], $bocoran['elv_615_t2_kode']);
        $pipa    = perhitunganQ_bocoran($bocoran['pipa_p1'], $bocoran['pipa_p1_kode']);

        // Simpan ke tabel perhitungan
        $this->perhitunganModel->save([
            'pengukuran_id' => $pengukuran_id,
            'talang1' => $talang1,
            'talang2' => $talang2,
            'pipa' => $pipa
        ]);

        return true;
    }
}
