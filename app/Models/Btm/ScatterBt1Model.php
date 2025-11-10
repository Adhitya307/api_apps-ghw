<?php
namespace App\Models\Btm;

use CodeIgniter\Model;

class ScatterBt1Model extends Model
{
    protected $DBGroup          = 'btm';
    protected $table            = 'p_scatter_bt_1';
    protected $primaryKey       = 'id_scatter';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_pengukuran', 'Y_US', 'X_TB', 'Y_cum', 'X_cum'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil data scatter by id_pengukuran
     */
    public function getByPengukuran($idPengukuran)
    {
        return $this->where('id_pengukuran', $idPengukuran)->first();
    }
    
    /**
     * Ambil semua data scatter untuk chart
     */
    public function getAllForChart()
    {
        return $this->select('p_scatter_bt_1.*, t.tahun, t.periode, t.tanggal')
                   ->join('t_pengukuran_btm t', 't.id_pengukuran = p_scatter_bt_1.id_pengukuran')
                   ->orderBy('t.tanggal ASC, t.tahun ASC, t.periode ASC')
                   ->findAll();
    }
    
    /**
     * Hapus data scatter by id_pengukuran
     */
    public function deleteByPengukuran($idPengukuran)
    {
        return $this->where('id_pengukuran', $idPengukuran)->delete();
    }
}