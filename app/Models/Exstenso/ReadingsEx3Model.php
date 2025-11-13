<?php

namespace App\Models\Exstenso;

use CodeIgniter\Model;

class ReadingsEx3Model extends Model
{
    protected $DBGroup          = 'db_exs';
    protected $table            = 'i_readings_Ex3';
    protected $primaryKey       = 'id_reading_ex3';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_pengukuran', 'reading_10', 'reading_20', 'reading_30'];

    protected $useTimestamps = false;
}
?>