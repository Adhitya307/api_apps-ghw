<?php

namespace App\Controllers\Rembesan;

use App\Controllers\BaseController;
use App\Helpers\Rembesan\AnalisaLookBurtHelper;
use App\Models\Rembesan\AnalisaLookBurtModel;
use App\Models\Rembesan\PerhitunganIntiGaleryModel;

class AnalisaLookBurt extends BaseController
{
    protected $helper;
    protected $analisaModel;
    protected $intiGaleryModel;

    public function __construct()
    {
        $this->helper           = new AnalisaLookBurtHelper();
        $this->analisaModel     = new AnalisaLookBurtModel();
        $this->intiGaleryModel  = new PerhitunganIntiGaleryModel();
    }

    /**
     * Hitung semua data yang ada di p_intigalery
     */
    public function hitungSemua()
    {
        try {
            $list = $this->intiGaleryModel->findAll();
            $hasilAll = [];

            foreach ($list as $row) {
                $pengukuran_id = $row['pengukuran_id'] ?? null;
                if (!$pengukuran_id) {
                    continue;
                }

                $hasil = $this->helper->hitungLookBurt($pengukuran_id);
                if (!$hasil) {
                    continue;
                }

                // Simpan / update ke database
                $existing = $this->analisaModel
                    ->where('pengukuran_id', $pengukuran_id)
                    ->first();

                if ($existing) {
                    $this->analisaModel->update($existing['id'], $hasil);
                } else {
                    $this->analisaModel->insert($hasil);
                }

                $hasilAll[] = $hasil;
            }

            return $this->response->setJSON([
                'status' => 'success',
                'total'  => count($hasilAll),
                'data'   => $hasilAll
            ]);

        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Hitung satu data berdasarkan pengukuran_id
     *
     * @param int  $pengukuran_id
     * @param bool $silent
     * @return \CodeIgniter\HTTP\ResponseInterface|array
     */
    public function hitung($pengukuran_id, $silent = false)
    {
        try {
            $hasil = $this->helper->hitungLookBurt($pengukuran_id);

            if (!$hasil) {
                $error = [
                    'status' => 'error',
                    'msg'    => "Data tidak ditemukan untuk pengukuran_id {$pengukuran_id}"
                ];
                return $silent ? $error : $this->response->setJSON($error);
            }

            // Simpan / update ke database
            $existing = $this->analisaModel
                ->where('pengukuran_id', $pengukuran_id)
                ->first();

            if ($existing) {
                $this->analisaModel->update($existing['id'], $hasil);
            } else {
                $this->analisaModel->insert($hasil);
            }

            $response = [
                'status' => 'success',
                'msg'    => 'Perhitungan Analisa Look Burt berhasil',
                'data'   => $hasil,
            ];

            return $silent ? $response : $this->response->setJSON($response);

        } catch (\Throwable $e) {
            $errorResponse = [
                'status' => 'error',
                'msg'    => $e->getMessage()
            ];
            return $silent ? $errorResponse : $this->response->setJSON($errorResponse);
        }
    }
}
