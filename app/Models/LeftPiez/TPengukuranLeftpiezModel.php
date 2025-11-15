<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;

class TPengukuranLeftpiezModel extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 't_pengukuran_Leftpiez';
    protected $primaryKey = 'id_pengukuran';
    protected $allowedFields = ['tahun', 'periode', 'tanggal', 'dma', 'temp_id', 'created_at', 'updated_at'];
}
