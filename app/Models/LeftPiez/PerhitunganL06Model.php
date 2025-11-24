<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;
use App\Models\LeftPiez\MetrikModel;

class PerhitunganL06Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'perhitungan_L_06';
    protected $primaryKey = 'id_perhitungan';
    protected $allowedFields = [
        'id_pengukuran',
        'Elv_Piez',
        'kedalaman',
        'record_max',
        'record_min',
        'koordinat_x',
        'koordinat_y',
        't_psmetrik_L06' // kolom baru untuk hasil perhitungan
    ];

    protected $useTimestamps = false;

    // Default value khusus L06
    protected $defaults = [
        'Elv_Piez'  => 690.09,
        'kedalaman' => 62,
        't_psmetrik_L06' => 0
    ];

    protected $metrikModel;

    public function __construct()
    {
        parent::__construct();
        $this->metrikModel = new MetrikModel();
    }

    /**
     * Hitung nilai l_06 seperti rumus Excel
     */
    public function hitungL06($id_pengukuran)
    {
        $metrik = $this->metrikModel->where('id_pengukuran', $id_pengukuran)->first();
        if (!$metrik) {
            return null;
        }

        $elv = $this->defaults['Elv_Piez'];
        $kedalaman = $this->defaults['kedalaman'];

        $existing = $this->where('id_pengukuran', $id_pengukuran)->first();
        if ($existing) {
            $elv = $existing['Elv_Piez'];
            $kedalaman = $existing['kedalaman'];
        }

        $l_06 = $metrik['l_06'] ?? null;

        // IFERROR logic: gunakan elv - l_06 jika ada, else elv - kedalaman
        $result = is_numeric($l_06) ? $elv - $l_06 : $elv - $kedalaman;

        return round($result, 4);
    }

    public function insert($data = null, bool $returnID = true)
    {
        $data = array_merge($this->defaults, (array) $data);

        if (isset($data['id_pengukuran'])) {
            $nilai = $this->hitungL06($data['id_pengukuran']);
         
            $data['t_psmetrik_L06'] = $nilai; // simpan di kolom baru
        }

        return parent::insert($data, $returnID);
    }
}
