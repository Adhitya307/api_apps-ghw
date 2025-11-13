<?php

namespace App\Models\Exstenso;

use CodeIgniter\Model;

class DeformasiEx3Model extends Model
{
    protected $DBGroup          = 'db_exs';
    protected $table            = 'p_deformasi_Ex3';
    protected $primaryKey       = 'id_deformasi_ex3';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_pengukuran', 'deformasi_10', 'deformasi_20', 'deformasi_30'];

    protected $useTimestamps = false;
}
?>