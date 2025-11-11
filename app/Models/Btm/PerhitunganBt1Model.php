<?php
namespace App\Models\Btm;

use CodeIgniter\Model;

class PerhitunganBt1Model extends Model
{
    protected $DBGroup          = 'btm';
    protected $table            = 'p_bt_1';
    protected $primaryKey       = 'id_perhitungan';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_pengukuran', 'A_sec', 'sin_A_rad', 'B_sec', 'sin_B_rad',
        'sin_C_rad', 'sin_C_deg', 'Cosa', 'a_rad', 'DMS'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByPengukuran($id_pengukuran)
    {
        return $this->where('id_pengukuran', $id_pengukuran)->first();
    }

    /**
     * PERHITUNGAN BT: Data pertama DIHITUNG (anggap data sebelumnya = 0)
     */
    public function hitungRumusBt1($id_pengukuran_sekarang, $bacaanBt1Model, $bacaanBt1Sebelumnya = null)
    {
        // Ambil data bacaan sekarang
        $bacaan_sekarang = $bacaanBt1Model->getByPengukuran($id_pengukuran_sekarang);
        
        if (!$bacaan_sekarang) {
            throw new \Exception('Data bacaan BT1 tidak ditemukan');
        }

        // PERHITUNGAN BT: Data pertama DIHITUNG (anggap data sebelumnya = 0)
        if (!$bacaanBt1Sebelumnya) {
            return $this->hitungDataPertama($bacaan_sekarang);
        }

        // =====================
        // RUMUS UNTUK DATA KEDUA DAN SETERUSNYA
        // =====================

        // 1. A_sec = SQRT((US_GP_sekarang - US_GP_sebelumnya)^2) * 1.14329
        $selisih_US = $bacaan_sekarang['US_GP'] - $bacaanBt1Sebelumnya['US_GP'];
        $A_sec = sqrt(pow($selisih_US, 2)) * 1.14329;
        
        // 2. Sin A (Rad) = SIN(RADIANS(A_sec * (1/3600)))
        $A_dalam_derajat = $A_sec * (1/3600);
        $A_dalam_radian = deg2rad($A_dalam_derajat);
        $sin_A_rad = sin($A_dalam_radian);

        // 3. B_sec = SQRT((TB_GP_sekarang - TB_GP_sebelumnya)^2) * 1.14466
        $selisih_TB = $bacaan_sekarang['TB_GP'] - $bacaanBt1Sebelumnya['TB_GP'];
        $B_sec = sqrt(pow($selisih_TB, 2)) * 1.14466;
        
        // 4. Sin B (Rad) = SIN(RADIANS(B_sec * (1/3600)))
        $B_dalam_derajat = $B_sec * (1/3600);
        $B_dalam_radian = deg2rad($B_dalam_derajat);
        $sin_B_rad = sin($B_dalam_radian);

        // 5. Sin C (Rad) = SQRT(Sin_A_rad^2 + Sin_B_rad^2)
        $sin_C_rad = sqrt(pow($sin_A_rad, 2) + pow($sin_B_rad, 2));
        
        // 6. Sin C (Deg) = DEGREES(ASIN(Sin_C_rad))
        $sin_C_deg = rad2deg(asin($sin_C_rad));

        // 7. Cosa = Sin_A_rad / Sin_C_rad
        $Cosa = ($sin_C_rad != 0) ? $sin_A_rad / $sin_C_rad : 0;
        
        // 8. a_rad = DEGREES(ACOS(Cosa))
        $a_rad = ($Cosa >= -1 && $Cosa <= 1) ? rad2deg(acos($Cosa)) : 0;

        // 9. DMS = Format Degree Minute Second
        $DMS = $this->decimalToDMS($a_rad);

        return [
            'id_pengukuran' => $id_pengukuran_sekarang,
            'A_sec' => $A_sec,
            'sin_A_rad' => $sin_A_rad,
            'B_sec' => $B_sec,
            'sin_B_rad' => $sin_B_rad,
            'sin_C_rad' => $sin_C_rad,
            'sin_C_deg' => $sin_C_deg,
            'Cosa' => $Cosa,
            'a_rad' => $a_rad,
            'DMS' => $DMS
        ];
    }

    /**
     * PERHITUNGAN BT: Hitung data pertama (anggap data sebelumnya = 0)
     */
    private function hitungDataPertama($bacaan_sekarang)
    {
        // Data pertama: Anggap data sebelumnya = 0
        $US_GP_sebelumnya = 0;
        $TB_GP_sebelumnya = 0;

        // 1. A_sec = SQRT((US_GP_sekarang - 0)^2) * 1.14329
        $selisih_US = $bacaan_sekarang['US_GP'] - $US_GP_sebelumnya;
        $A_sec = sqrt(pow($selisih_US, 2)) * 1.14329;
        
        // 2. Sin A (Rad) = SIN(RADIANS(A_sec * (1/3600)))
        $A_dalam_derajat = $A_sec * (1/3600);
        $A_dalam_radian = deg2rad($A_dalam_derajat);
        $sin_A_rad = sin($A_dalam_radian);

        // 3. B_sec = SQRT((TB_GP_sekarang - 0)^2) * 1.14466
        $selisih_TB = $bacaan_sekarang['TB_GP'] - $TB_GP_sebelumnya;
        $B_sec = sqrt(pow($selisih_TB, 2)) * 1.14466;
        
        // 4. Sin B (Rad) = SIN(RADIANS(B_sec * (1/3600)))
        $B_dalam_derajat = $B_sec * (1/3600);
        $B_dalam_radian = deg2rad($B_dalam_derajat);
        $sin_B_rad = sin($B_dalam_radian);

        // 5. Sin C (Rad) = SQRT(Sin_A_rad^2 + Sin_B_rad^2)
        $sin_C_rad = sqrt(pow($sin_A_rad, 2) + pow($sin_B_rad, 2));
        
        // 6. Sin C (Deg) = DEGREES(ASIN(Sin_C_rad))
        $sin_C_deg = rad2deg(asin($sin_C_rad));

        // 7. Cosa = Sin_A_rad / Sin_C_rad
        $Cosa = ($sin_C_rad != 0) ? $sin_A_rad / $sin_C_rad : 0;
        
        // 8. a_rad = DEGREES(ACOS(Cosa))
        $a_rad = ($Cosa >= -1 && $Cosa <= 1) ? rad2deg(acos($Cosa)) : 0;

        // 9. DMS = Format Degree Minute Second
        $DMS = $this->decimalToDMS($a_rad);

        return [
            'id_pengukuran' => $bacaan_sekarang['id_pengukuran'],
            'A_sec' => $A_sec,
            'sin_A_rad' => $sin_A_rad,
            'B_sec' => $B_sec,
            'sin_B_rad' => $sin_B_rad,
            'sin_C_rad' => $sin_C_rad,
            'sin_C_deg' => $sin_C_deg,
            'Cosa' => $Cosa,
            'a_rad' => $a_rad,
            'DMS' => $DMS
        ];
    }

    private function decimalToDMS($decimal)
    {
        if ($decimal == 0) {
            return "0° 0' 0\"";
        }
        
        $degrees = floor($decimal);
        $decimal_minutes = ($decimal - $degrees) * 60;
        $minutes = floor($decimal_minutes);
        $seconds = round(($decimal_minutes - $minutes) * 60, 1);
        
        return "{$degrees}° {$minutes}' {$seconds}\"";
    }

        public function getAllPerhitunganBt1()
    {
        return $this->findAll();
    }
}