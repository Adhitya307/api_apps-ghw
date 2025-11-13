<?php

namespace App\Models\Exstenso;

use CodeIgniter\Model;

class PengukuranEksModel extends Model
{
    protected $DBGroup          = 'db_exs';
    protected $table            = 't_pengukuran_eks';
    protected $primaryKey       = 'id_pengukuran';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['tahun', 'periode', 'tanggal', 'dma', 'temp_id', 'created_at', 'updated_at'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;
}
?>