<?php
namespace App\Models\Btm;

use CodeIgniter\Model;

class BacaanBt2Model extends Model
{
    protected $DBGroup          = 'btm';
    protected $table            = 't_bacaan_bt_2';
    protected $primaryKey       = 'id_bacaan';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_pengukuran', 'US_GP', 'US_Arah', 'TB_GP', 'TB_Arah'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ✅ TAMBAHKAN METHOD INI - YANG DIPANGGIL DI CONTROLLER
    public function getAllBacaan()
    {
        return $this->findAll();
    }

    public function getByPengukuran($id_pengukuran)
    {
        return $this->where('id_pengukuran', $id_pengukuran)->first();
    }
}