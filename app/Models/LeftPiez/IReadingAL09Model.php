<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;

class IReadingAL09Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'i_reading_A_L_09';
    protected $primaryKey = 'id_reading_A';
    protected $allowedFields = ['id_pengukuran', 'Elv_Piez'];
}
