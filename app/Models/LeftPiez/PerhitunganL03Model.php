<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;

class PerhitunganL03Model extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'perhitungan_L_03';
    protected $primaryKey = 'id_perhitungan';
    protected $allowedFields = ['id_pengukuran', 'Elv_Piez', 'kedalaman', 'record_max', 'record_min', 'koordinat_x', 'koordinat_y'];
}
