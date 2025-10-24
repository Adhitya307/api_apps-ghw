<?php
namespace App\Models\DomBody;

use CodeIgniter\Model;

class DepthElv625Model extends Model
{
    protected $DBGroup = 'hdm'; // ✅ WAJIB agar ikut koneksi HDM
    protected $table = 't_depth_elv625';
    protected $primaryKey = 'id_depth';
    protected $allowedFields = ['id_pengukuran', 'hv_1', 'hv_2', 'hv_3'];

    public function insertDefault($pengukuran_id)
    {
        return $this->insert([
            'id_pengukuran' => $pengukuran_id,
            'hv_1' => 20,
            'hv_2' => 40,
            'hv_3' => 50
        ]);
    }
}
