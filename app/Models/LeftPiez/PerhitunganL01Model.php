<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;
use App\Models\LeftPiez\MetrikModel;

class PerhitunganL01Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'perhitungan_L_01';
    protected $primaryKey = 'id_perhitungan';
    protected $allowedFields = [
        'id_pengukuran',
        'elv_piez',
        'kedalaman',
        'record_max',
        'record_min',
        'koordinat_x',
        'koordinat_y'
    ];

    protected $useTimestamps = false;

    // Default value
    protected $defaults = [
        'elv_piez'  => 650.64,
        'kedalaman' => 71.15
    ];

    protected $metrikModel;

    public function __construct()
    {
        parent::__construct();
        $this->metrikModel = new MetrikModel();
    }

    /**
     * Hitung nilai l_01 seperti rumus Excel
     */
    public function hitungL01($id_pengukuran)
    {
        // Ambil data dari MetrikModel
        $metrik = $this->metrikModel->where('id_pengukuran', $id_pengukuran)->first();

        if (!$metrik) {
            return null; // jika tidak ada data, return null
        }

        // Ambil nilai elv_piez dan kedalaman
        $elv = $this->defaults['elv_piez'];
        $kedalaman = $this->defaults['kedalaman'];

        // Jika data sudah ada di database Perhitungan, override default
        $existing = $this->where('id_pengukuran', $id_pengukuran)->first();
        if ($existing) {
            $elv = $existing['elv_piez'];
            $kedalaman = $existing['kedalaman'];
        }

        // Ambil nilai l_01 dari MetrikModel
        $l_01 = isset($metrik['l_01']) ? $metrik['l_01'] : null;

        // Rumus IFERROR: jika l_01 ada, gunakan elv - l_01, jika error atau null, gunakan elv - kedalaman
        try {
            if (is_numeric($l_01)) {
                $result = $elv - $l_01;
            } else {
                $result = $elv - $kedalaman;
            }
        } catch (\Exception $e) {
            $result = $elv - $kedalaman;
        }

        return round($result, 4);
    }

public function insert($data = null, bool $returnID = true)
{
    $data = array_merge($this->defaults, (array) $data);

    if (isset($data['id_pengukuran'])) {
        $nilai = $this->hitungL01($data['id_pengukuran']);

      
        $data['t_psmetrik_L01'] = $nilai; // simpan juga di kolom baru
    }

    return parent::insert($data, $returnID);
}

}
