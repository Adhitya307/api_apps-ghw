<?php

namespace App\Models\Exstenso;

use CodeIgniter\Model;

class PembacaanEx3Model extends Model
{
    protected $DBGroup          = 'db_exs';
    protected $table            = 't_pembacaan_Ex3';
    protected $primaryKey       = 'id_pembacaan_ex3';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_pengukuran', 'pembacaan_10', 'pembacaan_20', 'pembacaan_30'];

    protected $useTimestamps = false;
}
?>