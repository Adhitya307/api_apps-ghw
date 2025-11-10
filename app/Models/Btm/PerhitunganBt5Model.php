<?php
namespace App\Models\Btm;

use CodeIgniter\Model;

class PerhitunganBt5Model extends Model
{
    protected $DBGroup          = 'btm';
    protected $table            = 'p_bt_5';
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

    public function hitungRumusBt5($id_pengukuran_sekarang, $bacaanBt5Model, $bacaanBt5Sebelumnya = null)
    {
        // Implementasi rumus untuk BT5 (sama seperti BT1 dengan koefisien berbeda)
        $bacaan_sekarang = $bacaanBt5Model->getByPengukuran($id_pengukuran_sekarang);
        
        if (!$bacaan_sekarang) {
            throw new \Exception('Data bacaan BT5 tidak ditemukan');
        }

        if (!$bacaanBt5Sebelumnya) {
            return $this->hitungTanpaDataSebelumnya($bacaan_sekarang);
        }

        // Rumus untuk BT5 (sesuaikan koefisien dengan kebutuhan)
        $selisih_US = $bacaan_sekarang['US_GP'] - $bacaanBt5Sebelumnya['US_GP'];
        $A_sec = sqrt(pow($selisih_US, 2)) * 1.14329; // Sesuaikan koefisien
        
        $A_dalam_derajat = $A_sec * (1/3600);
        $A_dalam_radian = deg2rad($A_dalam_derajat);
        $sin_A_rad = sin($A_dalam_radian);

        $selisih_TB = $bacaan_sekarang['TB_GP'] - $bacaanBt5Sebelumnya['TB_GP'];
        $B_sec = sqrt(pow($selisih_TB, 2)) * 1.14466; // Sesuaikan koefisien
        
        $B_dalam_derajat = $B_sec * (1/3600);
        $B_dalam_radian = deg2rad($B_dalam_derajat);
        $sin_B_rad = sin($B_dalam_radian);

        $sin_C_rad = sqrt(pow($sin_A_rad, 2) + pow($sin_B_rad, 2));
        $sin_C_deg = rad2deg(asin($sin_C_rad));

        $Cosa = ($sin_C_rad != 0) ? $sin_A_rad / $sin_C_rad : 0;
        $a_rad = ($Cosa >= -1 && $Cosa <= 1) ? rad2deg(acos($Cosa)) : 0;

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

    private function hitungTanpaDataSebelumnya($bacaan_sekarang)
    {
        return [
            'id_pengukuran' => $bacaan_sekarang['id_pengukuran'],
            'A_sec' => 0,
            'sin_A_rad' => 0,
            'B_sec' => 0,
            'sin_B_rad' => 0,
            'sin_C_rad' => 0,
            'sin_C_deg' => 0,
            'Cosa' => 0,
            'a_rad' => 0,
            'DMS' => '0° 0\' 0"'
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
}