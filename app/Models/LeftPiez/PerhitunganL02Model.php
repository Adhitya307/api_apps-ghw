<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;
use App\Models\LeftPiez\MetrikModel;

class PerhitunganL02Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'perhitungan_L_02';
    protected $primaryKey = 'id_perhitungan';
    protected $allowedFields = [
        'id_pengukuran',
        'Elv_Piez',
        'kedalaman',
        'record_max',
        'record_min',
        'koordinat_x',
        'koordinat_y',
        't_psmetrik_L02' // kolom baru untuk hasil perhitungan
    ];

    protected $useTimestamps = false;

    // Default value khusus L02
    protected $defaults = [
        'Elv_Piez'  => 650.66,
        'kedalaman' => 73,
        't_psmetrik_L02' => 0 // default awal kolom baru
    ];

    protected $metrikModel;

    public function __construct()
    {
        parent::__construct();
        $this->metrikModel = new MetrikModel();
    }

    /**
     * Hitung nilai l_02 seperti rumus Excel
     */
    public function hitungL02($id_pengukuran)
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

        $l_02 = isset($metrik['l_02']) ? $metrik['l_02'] : null;

        // Rumus IFERROR: jika l_02 ada, gunakan elv - l_02, jika error/null gunakan elv - kedalaman
        $result = is_numeric($l_02) ? $elv - $l_02 : $elv - $kedalaman;

        return round($result, 4);
    }

public function insert($data = null, bool $returnID = true)
{
    $data = array_merge($this->defaults, (array) $data);

    if (isset($data['id_pengukuran'])) {
        $nilai = $this->hitungL02($data['id_pengukuran']);
        $data['t_psmetrik_L02'] = $nilai;
    }

    return parent::insert($data, $returnID);
}
}
