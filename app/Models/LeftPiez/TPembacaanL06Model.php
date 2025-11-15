<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;

class TPembacaanL06Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 't_pembacaan_L_06';
    protected $primaryKey = 'id_pembacaan';
    protected $allowedFields = ['id_pengukuran', 'feet', 'inch'];
    
    protected $validationRules = [
        'feet' => 'permit_empty|string',
        'inch' => 'permit_empty|decimal'
    ];
}