<?php
namespace App\Controllers\LeftPiez;

use App\Controllers\BaseController;
use App\Models\LeftPiez\MetrikModel;

// Perhitungan
use App\Models\LeftPiez\PerhitunganL01Model;
use App\Models\LeftPiez\PerhitunganL02Model;
use App\Models\LeftPiez\PerhitunganL03Model;
use App\Models\LeftPiez\PerhitunganL04Model;
use App\Models\LeftPiez\PerhitunganL05Model;
use App\Models\LeftPiez\PerhitunganL06Model;
use App\Models\LeftPiez\PerhitunganL07Model;
use App\Models\LeftPiez\PerhitunganL08Model;
use App\Models\LeftPiez\PerhitunganL09Model;
use App\Models\LeftPiez\PerhitunganL10Model;
use App\Models\LeftPiez\PerhitunganSPZ02Model;

// Pembacaan
use App\Models\LeftPiez\TPembacaanL01Model;
use App\Models\LeftPiez\TPembacaanL02Model;
use App\Models\LeftPiez\TPembacaanL03Model;
use App\Models\LeftPiez\TPembacaanL04Model;
use App\Models\LeftPiez\TPembacaanL05Model;
use App\Models\LeftPiez\TPembacaanL06Model;
use App\Models\LeftPiez\TPembacaanL07Model;
use App\Models\LeftPiez\TPembacaanL08Model;
use App\Models\LeftPiez\TPembacaanL09Model;
use App\Models\LeftPiez\TPembacaanL10Model;
use App\Models\LeftPiez\TPembacaanSPZ02Model;

class HitungLeft extends BaseController
{
    protected $metrikModel;

    protected $mapping = [
        'l_01' => ['perhitungan' => PerhitunganL01Model::class, 'pembacaan' => TPembacaanL01Model::class],
        'l_02' => ['perhitungan' => PerhitunganL02Model::class, 'pembacaan' => TPembacaanL02Model::class],
        'l_03' => ['perhitungan' => PerhitunganL03Model::class, 'pembacaan' => TPembacaanL03Model::class],
        'l_04' => ['perhitungan' => PerhitunganL04Model::class, 'pembacaan' => TPembacaanL04Model::class],
        'l_05' => ['perhitungan' => PerhitunganL05Model::class, 'pembacaan' => TPembacaanL05Model::class],
        'l_06' => ['perhitungan' => PerhitunganL06Model::class, 'pembacaan' => TPembacaanL06Model::class],
        'l_07' => ['perhitungan' => PerhitunganL07Model::class, 'pembacaan' => TPembacaanL07Model::class],
        'l_08' => ['perhitungan' => PerhitunganL08Model::class, 'pembacaan' => TPembacaanL08Model::class],
        'l_09' => ['perhitungan' => PerhitunganL09Model::class, 'pembacaan' => TPembacaanL09Model::class],
        'l_10' => ['perhitungan' => PerhitunganL10Model::class, 'pembacaan' => TPembacaanL10Model::class],
        'spz_02' => ['perhitungan' => PerhitunganSPZ02Model::class, 'pembacaan' => TPembacaanSPZ02Model::class],
    ];

    public function __construct()
    {
        $this->metrikModel = new MetrikModel();
    }

    /**
     * Hitung semua kolom berdasarkan data pembacaan user
     */
public function hitungSemua($idPengukuran)
{
    $dataUpdate = [
        'id_pengukuran' => $idPengukuran,
        'M_feet' => MetrikModel::FEET_DEFAULT,
        'M_inch' => MetrikModel::INCH_DEFAULT,
        'l_01' => null,
        'l_02' => null,
        'l_03' => null,
        'l_04' => null,
        'l_05' => null,
        'l_06' => null,
        'l_07' => null,
        'l_08' => null,
        'l_09' => null,
        'l_10' => null,
        'spz_02' => null,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    foreach ($this->mapping as $kolom => $models) {
        $pembacaanModel = new $models['pembacaan']();
        $perhitunganModel = new $models['perhitungan']();

        // Ambil kedalaman default
        $perhitungan = $perhitunganModel->where('id_pengukuran', $idPengukuran)->first();
        $kedalaman = $perhitungan['kedalaman'] ?? 71.15;

        // Ambil semua pembacaan untuk kolom ini
        $rows = $pembacaanModel->where('id_pengukuran', $idPengukuran)->findAll();

        foreach ($rows as $row) {
            if ($row) {
                $dataUpdate[$kolom] = $this->metrikModel->hitungL($row['feet'], $row['inch'], $kedalaman);
                // setiap kolom hanya diupdate 1x (yang terakhir jika banyak)
            }
        }
    }

    // Simpan 1 row saja
    $this->metrikModel->save($dataUpdate);

    return $this->response->setJSON([
        'status' => 'success',
        'id_pengukuran' => $idPengukuran,
        'message' => 'Semua kolom digabung menjadi 1 row'
    ]);
}
}
