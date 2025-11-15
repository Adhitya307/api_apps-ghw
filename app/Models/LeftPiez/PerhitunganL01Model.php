<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;

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

    public function insert($data = null, bool $returnID = true)
    {
        // Terapkan default jika tidak dikirim dari controller
        $data = array_merge($this->defaults, (array) $data);

        return parent::insert($data, $returnID);
    }
}
