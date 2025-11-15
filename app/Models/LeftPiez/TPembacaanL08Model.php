<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;

class TPembacaanL08Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 't_pembacaan_L_08';
    protected $primaryKey = 'id_pembacaan';
    protected $allowedFields = ['id_pengukuran', 'feet', 'inch'];
    
    protected $validationRules = [
        'feet' => 'permit_empty',
        'inch' => 'permit_empty|decimal'
    ];
}