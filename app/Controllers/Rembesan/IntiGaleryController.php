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
}
