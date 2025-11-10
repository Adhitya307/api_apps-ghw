<?php

namespace App\Models\Btm;

use CodeIgniter\Model;

class PerhitunganBt4Model extends Model
{
    protected $DBGroup          = 'btm';
    protected $table            = 'p_bt_4';
    protected $primaryKey       = 'id_perhitungan';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_pengukuran', 'A_sec', 'sin_A_rad', 'B_sec', 'sin_B_rad',
        'sin_C_rad', 'sin_C_deg', 'Cosa', 'a_rad', 'DMS'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Get perhitungan by id_pengukuran
    public function getByPengukuran($id_pengukuran)
    {
        return $this->where('id_pengukuran', $id_pengukuran)->first();
    }

    // Hitung rumus p_bt_4 berdasarkan data bacaan
    public function hitungRumusBt4($id_pengukuran_sekarang, $bacaanBt4Model, $bacaanBt4Sebelumnya = null)
    {
        // Ambil data bacaan sekarang
        $bacaan_sekarang = $bacaanBt4Model->getByPengukuran($id_pengukuran_sekarang);
        
        if (!$bacaan_sekarang) {
            throw new \Exception('Data bacaan BT4 tidak ditemukan');
        }

        // Jika tidak ada data sebelumnya, gunakan nilai default
        if (!$bacaanBt4Sebelumnya) {
            return $this->hitungDenganDefault($bacaan_sekarang);
        }

        // =====================
        // RUMUS SESUAI EXCEL UNTUK BT4
        // =====================

        // 1. A_sec = SQRT((US_GP_sekarang - US_GP_sebelumnya)^2) * 1.14329
        // SAMA dengan BT1 & BT2
        $selisih_US = $bacaan_sekarang['US_GP'] - $bacaanBt4Sebelumnya['US_GP'];
        $A_sec = sqrt(pow($selisih_US, 2)) * 1.14329;
        
        // 2. Sin A (Rad) = SIN(RADIANS(A_sec * (1/3600)))
        // SAMA dengan semua BT
        $A_dalam_derajat = $A_sec * (1/3600);
        $A_dalam_radian = deg2rad($A_dalam_derajat);
        $sin_A_rad = sin($A_dalam_radian);

        // 3. B_sec = SQRT((TB_GP_sekarang - TB_GP_sebelumnya)^2) * 1.14375
        // SAMA dengan BT3
        $selisih_TB = $bacaan_sekarang['TB_GP'] - $bacaanBt4Sebelumnya['TB_GP'];
        $B_sec = sqrt(pow($selisih_TB, 2)) * 1.14375;
        
        // 4. Sin B (Rad) = SIN(RADIANS(B_sec * (1/3600)))
        // SAMA dengan semua BT
        $B_dalam_derajat = $B_sec * (1/3600);
        $B_dalam_radian = deg2rad($B_dalam_derajat);
        $sin_B_rad = sin($B_dalam_radian);

        // 5. Sin C (Rad) = SQRT(Sin_A_rad^2 + Sin_B_rad^2)
        // SAMA dengan semua BT
        $sin_C_rad = sqrt(pow($sin_A_rad, 2) + pow($sin_B_rad, 2));
        
        // 6. Sin C (Deg) = DEGREES(ASIN(Sin_C_rad))
        // SAMA dengan semua BT
        $sin_C_deg = rad2deg(asin($sin_C_rad));

        // 7. Cosa = Sin_A_rad / Sin_C_rad
        // SAMA dengan semua BT
        $Cosa = ($sin_C_rad != 0) ? $sin_A_rad / $sin_C_rad : 0;
        
        // 8. a_rad = DEGREES(ACOS(Cosa))
        // SAMA dengan semua BT
        $a_rad = ($Cosa >= -1 && $Cosa <= 1) ? rad2deg(acos($Cosa)) : 0;

        // 9. DMS = TEXT(INT(a_rad);"0° ")&TEXT(INT((a_rad-INT(a_rad))*60);"0' ")&TEXT((a_rad*60-INT(a_rad*60))*60;"0.0")&CHAR(34)
        // SAMA dengan semua BT
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

    // Jika tidak ada data sebelumnya
    private function hitungDenganDefault($bacaan_sekarang)
    {
        // Gunakan nilai default
        // SAMA dengan BT1 & BT2
        $A_sec = $bacaan_sekarang['US_GP'] * 1.14329;
        $A_dalam_derajat = $A_sec * (1/3600);
        $A_dalam_radian = deg2rad($A_dalam_derajat);
        $sin_A_rad = sin($A_dalam_radian);
        
        // SAMA dengan BT3
        $B_sec = $bacaan_sekarang['TB_GP'] * 1.14375;
        $B_dalam_derajat = $B_sec * (1/3600);
        $B_dalam_radian = deg2rad($B_dalam_derajat);
        $sin_B_rad = sin($B_dalam_radian);
        
        $sin_C_rad = sqrt(pow($sin_A_rad, 2) + pow($sin_B_rad, 2));
        $sin_C_deg = rad2deg(asin($sin_C_rad));
        
        $Cosa = ($sin_C_rad != 0) ? $sin_A_rad / $sin_C_rad : 0;
        $a_rad = ($Cosa >= -1 && $Cosa <= 1) ? rad2deg(acos($Cosa)) : 0;
        
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

    // Konversi decimal ke Degree Minute Second (sesuai rumus Excel)
    private function decimalToDMS($decimal)
    {
        $degrees = floor($decimal);
        $decimal_minutes = ($decimal - $degrees) * 60;
        $minutes = floor($decimal_minutes);
        $seconds = round(($decimal_minutes - $minutes) * 60, 1);
        
        return "{$degrees}° {$minutes}' {$seconds}\"";
    }

    // Simpan hasil perhitungan BT4
    public function simpanPerhitunganBt4($data)
    {
        return $this->insert($data);
    }

    // Update hasil perhitungan BT4
    public function updatePerhitunganBt4($id_perhitungan, $data)
    {
        return $this->update($id_perhitungan, $data);
    }

    // Hapus hasil perhitungan BT4
    public function hapusPerhitunganBt4($id_perhitungan)
    {
        return $this->delete($id_perhitungan);
    }

    // Get semua perhitungan BT4
    public function getAllPerhitunganBt4()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }
}