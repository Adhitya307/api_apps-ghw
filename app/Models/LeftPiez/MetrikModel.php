<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;

class MetrikModel extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'b_piezo_metrik';
    protected $primaryKey = 'id_bacaan_metrik';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'id_pengukuran','M_feet','M_inch',
        'l_01','l_02','l_03','l_04','l_05',
        'l_06','l_07','l_08','l_09','l_10',
        'spz_02','created_at','updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    const FEET_DEFAULT = 0.3048;
    const INCH_DEFAULT = 0.0254;

    /**
     * Hitung nilai feet/inch menjadi meter
     */
    public function hitungL($feet, $inch, $kedalaman = null)
    {
        if (is_string($feet) && strtoupper(trim($feet)) === 'KERING') {
            return (float)$kedalaman;
        }

        $feetValue = is_numeric($feet) ? (float)str_replace(',', '.', $feet) : 0;
        $inchValue = is_numeric($inch) ? (float)str_replace(',', '.', $inch) : 0;

        $result = ($feetValue * self::FEET_DEFAULT) + ($inchValue * self::INCH_DEFAULT);
        return round($result, 4);
    }
}
