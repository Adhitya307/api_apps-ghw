<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;

class IReadingBSpz02Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'i_reading_B_SPZ_02';
    protected $primaryKey = 'id_reading_B';
    protected $allowedFields = ['id_pengukuran', 'Elv_Piez'];
}
