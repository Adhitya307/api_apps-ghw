<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;

class TPembacaanL04Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 't_pembacaan_L_04';
    protected $primaryKey = 'id_pembacaan';
    protected $allowedFields = ['id_pengukuran', 'feet', 'inch'];
    
    protected $validationRules = [
        'feet' => 'permit_empty',
        'inch' => 'permit_empty|decimal'
    ];
}