<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;
use App\Models\LeftPiez\MetrikModel;

class PerhitunganL03Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'perhitungan_L_03';
    protected $primaryKey = 'id_perhitungan';
    protected $allowedFields = [
        'id_pengukuran',
        'Elv_Piez',
        'kedalaman',
        'record_max',
        'record_min',
        'koordinat_x',
        'koordinat_y',
        't_psmetrik_L03' // kolom baru untuk hasil perhitungan
    ];

    protected $useTimestamps = false;

    // Default value khusus L03
    protected $defaults = [
        'Elv_Piez'  => 616.55,
        'kedalaman' => 59,
        't_psmetrik_L03' => 0
    ];

    protected $metrikModel;

    public function __construct()
    {
        parent::__construct();
        $this->metrikModel = new MetrikModel();
    }

    /**
     * Hitung nilai l_03 seperti rumus Excel
     */
    public function hitungL03($id_pengukuran)
    {
        $metrik = $this->metrikModel->where('id_pengukuran', $id_pengukuran)->first();
        if (!$metrik) {
            return null;
        }

        $elv = $this->defaults['Elv_Piez'];
        $kedalaman = $this->defaults['kedalaman'];

        // Jika data Perhitungan sudah ada, override default
        $existing = $this->where('id_pengukuran', $id_pengukuran)->first();
        if ($existing) {
            $elv = $existing['Elv_Piez'];
            $kedalaman = $existing['kedalaman'];
        }

        $l_03 = $metrik['l_03'] ?? null;

        // IFERROR logic: jika l_03 ada gunakan elv - l_03, jika kosong gunakan elv - kedalaman
        $result = is_numeric($l_03) ? $elv - $l_03 : $elv - $kedalaman;

        return round($result, 4);
    }

    public function insert($data = null, bool $returnID = true)
    {
        $data = array_merge($this->defaults, (array) $data);

        if (isset($data['id_pengukuran'])) {
            $nilai = $this->hitungL03($data['id_pengukuran']);

            $data['t_psmetrik_L03'] = $nilai; // simpan di kolom baru
        }

        return parent::insert($data, $returnID);
    }
}
