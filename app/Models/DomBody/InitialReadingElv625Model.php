<?php
namespace App\Models\DomBody;

use CodeIgniter\Model;

class InitialReadingElv625Model extends Model
{
    protected $DBGroup = 'hdm';
    protected $table = 'm_initial_reading_elv_625';
    protected $primaryKey = 'id_initial_reading';
    protected $allowedFields = ['id_pengukuran', 'hv_1', 'hv_2', 'hv_3'];

    public function insertDefault($pengukuran_id)
    {
        return $this->insert([
            'id_pengukuran' => $pengukuran_id,
            'hv_1' => 36.00,
            'hv_2' => 35.50,
            'hv_3' => 35.00
        ]);
    }
}
