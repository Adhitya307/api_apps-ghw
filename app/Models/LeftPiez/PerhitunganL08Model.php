<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;
use App\Models\LeftPiez\MetrikModel;

class PerhitunganL08Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'perhitungan_L_08';
    protected $primaryKey = 'id_perhitungan';
    protected $allowedFields = [
        'id_pengukuran',
        'Elv_Piez',
        'kedalaman',
        'record_max',
        'record_min',
        'koordinat_x',
        'koordinat_y',
        't_psmetrik_L08' // kolom baru
    ];

    protected $useTimestamps = false;

    // Default value khusus L08
    protected $defaults = [
        'Elv_Piez'  => 659.14,
        'kedalaman' => 55.5,
        't_psmetrik_L08' => 0
    ];

    protected $metrikModel;

    public function __construct()
    {
        parent::__construct();
        $this->metrikModel = new MetrikModel();
    }

    /**
     * Hitung nilai l_08 seperti rumus Excel
     */
    public function hitungL08($id_pengukuran)
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

        $l_08 = $metrik['l_08'] ?? null;

        // IFERROR logic: gunakan elv - l_08 jika ada, else elv - kedalaman
        $result = is_numeric($l_08) ? $elv - $l_08 : $elv - $kedalaman;

        return round($result, 4);
    }

    public function insert($data = null, bool $returnID = true)
    {
        $data = array_merge($this->defaults, (array) $data);

        if (isset($data['id_pengukuran'])) {
            $nilai = $this->hitungL08($data['id_pengukuran']);
            
            $data['t_psmetrik_L08'] = $nilai; // simpan di kolom baru
        }

        return parent::insert($data, $returnID);
    }
}
