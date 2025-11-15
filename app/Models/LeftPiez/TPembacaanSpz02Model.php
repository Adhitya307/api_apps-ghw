<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;

class TPembacaanSpz02Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 't_pembacaan_SPZ_02';
    protected $primaryKey = 'id_pembacaan';
    protected $allowedFields = ['id_pengukuran', 'feet', 'inch'];
}
