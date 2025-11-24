<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;
use App\Models\LeftPiez\MetrikModel;

class PerhitunganSpz02Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'perhitungan_SPZ_02';
    protected $primaryKey = 'id_perhitungan';
    protected $allowedFields = [
        'id_pengukuran',
        'Elv_Piez',
        'kedalaman',
        'record_max',
        'record_min',
        'koordinat_x',
        'koordinat_y',
        't_psmetrik_SPZ02' // kolom baru
    ];

    protected $useTimestamps = false;

    // Default value khusus SPZ_02
    protected $defaults = [
        'Elv_Piez'  => 700.08,
        'kedalaman' => 70,
        't_psmetrik_SPZ02' => 0
    ];

    protected $metrikModel;

    public function __construct()
    {
        parent::__construct();
        $this->metrikModel = new MetrikModel();
    }

    /**
     * Hitung nilai SPZ_02 seperti rumus Excel
     */
    public function hitungSPZ02($id_pengukuran)
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

        $spz_02 = $metrik['spz_02'] ?? null;

        // IFERROR logic: gunakan elv - spz_02 jika ada, else elv - kedalaman
        $result = is_numeric($spz_02) ? $elv - $spz_02 : $elv - $kedalaman;

        return round($result, 4);
    }

    public function insert($data = null, bool $returnID = true)
    {
        $data = array_merge($this->defaults, (array) $data);

        if (isset($data['id_pengukuran'])) {
            $nilai = $this->hitungSPZ02($data['id_pengukuran']);
            
            $data['t_psmetrik_SPZ02'] = $nilai; // simpan di kolom baru
        }

        return parent::insert($data, $returnID);
    }
}
