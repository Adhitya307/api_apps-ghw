<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;
use App\Models\LeftPiez\MetrikModel;

class PerhitunganL04Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'perhitungan_L_04';
    protected $primaryKey = 'id_perhitungan';
    protected $allowedFields = [
        'id_pengukuran',
        'Elv_Piez',
        'kedalaman',
        'record_max',
        'record_min',
        'koordinat_x',
        'koordinat_y',
        't_psmetrik_L04' // kolom baru untuk hasil perhitungan
    ];

    protected $useTimestamps = false;

    // Default value khusus L04
    protected $defaults = [
        'Elv_Piez'  => 580.26,
        'kedalaman' => 50,
        't_psmetrik_L04' => 0
    ];

    protected $metrikModel;

    public function __construct()
    {
        parent::__construct();
        $this->metrikModel = new MetrikModel();
    }

    /**
     * Hitung nilai l_04 seperti rumus Excel
     */
    public function hitungL04($id_pengukuran)
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

        $l_04 = $metrik['l_04'] ?? null;

        // IFERROR logic: gunakan elv - l_04 jika ada, else elv - kedalaman
        $result = is_numeric($l_04) ? $elv - $l_04 : $elv - $kedalaman;

        return round($result, 4);
    }

    public function insert($data = null, bool $returnID = true)
    {
        $data = array_merge($this->defaults, (array) $data);

        if (isset($data['id_pengukuran'])) {
            $nilai = $this->hitungL04($data['id_pengukuran']);

            $data['t_psmetrik_L04'] = $nilai; // simpan di kolom baru
        }

        return parent::insert($data, $returnID);
    }
}
