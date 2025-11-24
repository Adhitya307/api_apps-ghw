<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;
use App\Models\LeftPiez\MetrikModel;

class PerhitunganL10Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'perhitungan_L_10';
    protected $primaryKey = 'id_perhitungan';
    protected $allowedFields = [
        'id_pengukuran',
        'Elv_Piez',
        'kedalaman',
        'record_max',
        'record_min',
        'koordinat_x',
        'koordinat_y',
        't_psmetrik_L10' // kolom baru
    ];

    protected $useTimestamps = false;

    // Default value khusus L10
    protected $defaults = [
        'Elv_Piez'  => 580.36,
        'kedalaman' => 51.5,
        't_psmetrik_L10' => 0
    ];

    protected $metrikModel;

    public function __construct()
    {
        parent::__construct();
        $this->metrikModel = new MetrikModel();
    }

    /**
     * Hitung nilai l_10 seperti rumus Excel
     */
    public function hitungL10($id_pengukuran)
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

        $l_10 = $metrik['l_10'] ?? null;

        // IFERROR logic: gunakan elv - l_10 jika ada, else elv - kedalaman
        $result = is_numeric($l_10) ? $elv - $l_10 : $elv - $kedalaman;

        return round($result, 4);
    }

    public function insert($data = null, bool $returnID = true)
    {
        $data = array_merge($this->defaults, (array) $data);

        if (isset($data['id_pengukuran'])) {
            $nilai = $this->hitungL10($data['id_pengukuran']);
          
            $data['t_psmetrik_L10'] = $nilai; // simpan di kolom baru
        }

        return parent::insert($data, $returnID);
    }
}
