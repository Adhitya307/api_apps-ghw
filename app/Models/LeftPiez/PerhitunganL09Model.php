<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;
use App\Models\LeftPiez\MetrikModel;

class PerhitunganL09Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'perhitungan_L_09';
    protected $primaryKey = 'id_perhitungan';
    protected $allowedFields = [
        'id_pengukuran',
        'Elv_Piez',
        'kedalaman',
        'record_max',
        'record_min',
        'koordinat_x',
        'koordinat_y',
        't_psmetrik_L09' // kolom baru
    ];

    protected $useTimestamps = false;

    // Default value khusus L09
    protected $defaults = [
        'Elv_Piez'  => 622.45,
        'kedalaman' => 57,
        't_psmetrik_L09' => 0
    ];

    protected $metrikModel;

    public function __construct()
    {
        parent::__construct();
        $this->metrikModel = new MetrikModel();
    }

    /**
     * Hitung nilai l_09 seperti rumus Excel
     */
    public function hitungL09($id_pengukuran)
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

        $l_09 = $metrik['l_09'] ?? null;

        // IFERROR logic: gunakan elv - l_09 jika ada, else elv - kedalaman
        $result = is_numeric($l_09) ? $elv - $l_09 : $elv - $kedalaman;

        return round($result, 4);
    }

    public function insert($data = null, bool $returnID = true)
    {
        $data = array_merge($this->defaults, (array) $data);

        if (isset($data['id_pengukuran'])) {
            $nilai = $this->hitungL09($data['id_pengukuran']);
            
            $data['t_psmetrik_L09'] = $nilai; // simpan di kolom baru
        }

        return parent::insert($data, $returnID);
    }
}
