<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;

class TPembacaanL05Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 't_pembacaan_L_05';
    protected $primaryKey = 'id_pembacaan';
    protected $allowedFields = ['id_pengukuran', 'feet', 'inch'];
}
