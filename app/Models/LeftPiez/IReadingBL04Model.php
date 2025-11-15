<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;

class IReadingBL04Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'i_reading_B_L_04';
    protected $primaryKey = 'id_reading_B';
    protected $allowedFields = ['id_pengukuran', 'Elv_Piez'];
}
