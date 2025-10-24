<?php
namespace App\Models\DomBody;

use CodeIgniter\Model;

class DepthElv600Model extends Model
{
    protected $DBGroup = 'hdm'; // ✅ WAJIB agar ikut koneksi HDM
    protected $table = 't_depth_elv600';
    protected $primaryKey = 'id_depth';
    protected $allowedFields = ['id_pengukuran', 'hv_1', 'hv_2', 'hv_3', 'hv_4', 'hv_5'];

    public function insertDefault($pengukuran_id)
    {
        return $this->insert([
            'id_pengukuran' => $pengukuran_id,
            'hv_1' => 10,
            'hv_2' => 30,
            'hv_3' => 50,
            'hv_4' => 70,
            'hv_5' => 84.5
        ]);
    }
}
