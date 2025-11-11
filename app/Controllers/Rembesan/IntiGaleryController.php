<?php
namespace App\Controllers\Rembesan;

use App\Controllers\BaseController;
use App\Models\Rembesan\PerhitunganIntiGaleryModel;

class IntiGaleryController extends BaseController
{
    protected $model;

    public function __construct()
    {
        helper('rembesan/rumus_intigalery');
        $this->model = new PerhitunganIntiGaleryModel();
    }

    /**
     * Proses perhitungan Inti Galery untuk 1 pengukuran
     *
     * @param int $pengukuranId
     * @return array|false
     */
    public function proses($pengukuranId)
    {
        log_message('debug', "[IntiGalery] ▶️ Mulai proses untuk ID {$pengukuranId}");

        $hasil = hitungIntiGalery((int) $pengukuranId);

        if ($hasil === false) {
            log_message('error', "[IntiGalery] ❌ Gagal hitung untuk ID {$pengukuranId}");
            return false;
        }

        // Tambahkan ID pengukuran ke hasil
        $hasil['pengukuran_id'] = $pengukuranId;

        // Simpan ke database
        $existing = $this->model->where('pengukuran_id', $pengukuranId)->first();

        if ($existing) {
            $this->model->update($existing['id'], $hasil);
            log_message('debug', "[IntiGalery] 🔄 Update DB untuk pengukuran_id={$pengukuranId} | Data=" . json_encode($hasil));
        } else {
            $this->model->insert($hasil);
            log_message('debug', "[IntiGalery] 🆕 Insert DB untuk pengukuran_id={$pengukuranId} | Data=" . json_encode($hasil));
        }

        log_message('debug', "[IntiGalery] ✅ Proses selesai untuk ID {$pengukuranId}");

        return $hasil;
    }

    /**
 * Ambil data Inti Gallery berdasarkan pengukuran_id
 * 
 * @return \CodeIgniter\HTTP\Response
 */
public function getIntiGallery()
{
    $pengukuranId = $this->request->getGet('pengukuran_id');
    
    if (!$pengukuranId) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Parameter pengukuran_id diperlukan'
        ])->setStatusCode(400);
    }

    log_message('debug', "[IntiGalery] 📥 GET data untuk pengukuran_id={$pengukuranId}");

    try {
        // Cari data di database
        $data = $this->model->where('pengukuran_id', $pengukuranId)->first();

        if (!$data) {
            log_message('debug', "[IntiGalery] ❌ Data tidak ditemukan untuk pengukuran_id={$pengukuranId}");
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data Inti Gallery tidak ditemukan'
            ])->setStatusCode(404);
        }

        // Format response
        $response = [
            'status' => 'success',
            'data' => [
                'a1' => (float)$data['a1'],
                'ambang_a1' => (float)$data['ambang_a1']
            ]
        ];

        log_message('debug', "[IntiGalery] ✅ Data ditemukan: a1={$data['a1']}, ambang_a1={$data['ambang_a1']}");

        return $this->response->setJSON($response);

    } catch (\Exception $e) {
        log_message('error', "[IntiGalery] ❌ Error get data: " . $e->getMessage());
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Terjadi kesalahan server'
        ])->setStatusCode(500);
    }
}
}
