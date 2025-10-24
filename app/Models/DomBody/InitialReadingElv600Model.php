<?php
namespace App\Models\DomBody;

use CodeIgniter\Model;

class InitialReadingElv600Model extends Model
{
    protected $DBGroup = 'hdm';
    protected $table = 'm_initial_reading_elv_600';
    protected $primaryKey = 'id_initial_reading';
    protected $allowedFields = ['id_pengukuran', 'hv_1', 'hv_2', 'hv_3', 'hv_4', 'hv_5'];

    public function insertDefault($pengukuran_id)
    {
        return $this->insert([
            'id_pengukuran' => $pengukuran_id,
            'hv_1' => 26.60,
            'hv_2' => 25.50,
            'hv_3' => 24.50,
            'hv_4' => 23.40,
            'hv_5' => 23.60
        ]);
    }
}
