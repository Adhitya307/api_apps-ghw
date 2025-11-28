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

    const FEET = 0.3048;
    const INCH = 0.0254;

    /**
     * Hitung nilai feet/inch menjadi meter
     * Menangani kondisi "kering" (text)
     */
    public function hitungL($feet, $inch, $kedalaman = null)
    {
        // Normalisasi menjadi huruf kecil tanpa spasi
        $feetNormalized = strtolower(trim($feet));

        // Jika kering → return kedalaman default
        if ($feetNormalized === 'kering') {
            return (float)$kedalaman;
        }

        // konversi numerik
        $feetValue = is_numeric($feet) ? floatval(str_replace(',', '.', $feet)) : 0;
        $inchValue = is_numeric($inch) ? floatval(str_replace(',', '.', $inch)) : 0;

        $result = ($feetValue * self::FEET) + ($inchValue * self::INCH);
        return round($result, 4);
    }
}
