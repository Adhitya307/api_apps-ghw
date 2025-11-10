<?php

namespace App\Models\Btm;

use CodeIgniter\Model;

class PerhitunganBt2Model extends Model
{
    protected $DBGroup          = 'btm';
    protected $table            = 'p_bt_2';
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

    // Hitung rumus p_bt_2 berdasarkan data bacaan
    // Hitung rumus p_bt_2 berdasarkan data bacaan
public function hitungRumusBt2($id_pengukuran_sekarang, $bacaanBt2Model, $bacaanBt2Sebelumnya = null)
{
    // DEBUG: Tampilkan data yang digunakan
    log_message('debug', "=== DEBUG PERHITUNGAN BT2 ===");
    log_message('debug', "ID Pengukuran Sekarang: " . $id_pengukuran_sekarang);
    
    $bacaan_sekarang = $bacaanBt2Model->getByPengukuran($id_pengukuran_sekarang);
    
    if (!$bacaan_sekarang) {
        throw new \Exception('Data bacaan BT2 tidak ditemukan');
    }

    // KONSTANTA YANG BENAR DAN SERAGAM
    $KONSTANTA_US = 1.14329;
    $KONSTANTA_TB = 1.14420; // ← KONSTANTA YANG BENAR UNTUK TB

    // DEBUG DATA
    log_message('debug', "Bacaan Sekarang - US_GP: " . ($bacaan_sekarang['US_GP'] ?? 'null') . 
                        ", TB_GP: " . ($bacaan_sekarang['TB_GP'] ?? 'null'));
    
    if ($bacaanBt2Sebelumnya) {
        log_message('debug', "Bacaan Sebelumnya - US_GP: " . ($bacaanBt2Sebelumnya['US_GP'] ?? 'null') . 
                            ", TB_GP: " . ($bacaanBt2Sebelumnya['TB_GP'] ?? 'null'));
        
        log_message('debug', "Selisih US: " . ($bacaan_sekarang['US_GP'] - $bacaanBt2Sebelumnya['US_GP']));
        log_message('debug', "Selisih TB: " . ($bacaan_sekarang['TB_GP'] - $bacaanBt2Sebelumnya['TB_GP']));
    }

    // Jika tidak ada data sebelumnya, gunakan nilai default
    if (!$bacaanBt2Sebelumnya) {
        return $this->hitungDenganDefault($bacaan_sekarang, $KONSTANTA_US, $KONSTANTA_TB);
    }

    // =====================
    // RUMUS SESUAI EXCEL UNTUK BT2 - DIPERBAIKI
    // =====================

    // 1. A_sec = ABS(US_GP_sekarang - US_GP_sebelumnya) * 1.14329
    $selisih_US = $bacaan_sekarang['US_GP'] - $bacaanBt2Sebelumnya['US_GP'];
    $A_sec = abs($selisih_US) * $KONSTANTA_US;
    
    // 2. Sin A (Rad) = SIN(RADIANS(A_sec * (1/3600)))
    $A_dalam_derajat = $A_sec * (1/3600);
    $A_dalam_radian = deg2rad($A_dalam_derajat);
    $sin_A_rad = sin($A_dalam_radian);

    // 3. B_sec = ABS(TB_GP_sekarang - TB_GP_sebelumnya) * 1.14420 ← KONSTANTA YANG BENAR
    $selisih_TB = $bacaan_sekarang['TB_GP'] - $bacaanBt2Sebelumnya['TB_GP'];
    $B_sec = abs($selisih_TB) * $KONSTANTA_TB;
    
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

    // 9. DMS = Konversi ke Degree Minute Second
    $DMS = $this->decimalToDMS($a_rad);

    // DEBUG HASIL
    log_message('debug', "HASIL PERHITUNGAN:");
    log_message('debug', "selisih_US: " . $selisih_US);
    log_message('debug', "A_sec: " . $A_sec);
    log_message('debug', "selisih_TB: " . $selisih_TB);
    log_message('debug', "B_sec: " . $B_sec);
    log_message('debug', "sin_A_rad: " . $sin_A_rad);
    log_message('debug', "sin_B_rad: " . $sin_B_rad);
    log_message('debug', "sin_C_rad: " . $sin_C_rad);
    log_message('debug', "sin_C_deg: " . $sin_C_deg);
    log_message('debug', "Cosa: " . $Cosa);
    log_message('debug', "a_rad: " . $a_rad);
    log_message('debug', "DMS: " . $DMS);
    log_message('debug', "=== END DEBUG ===");

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

// Jika tidak ada data sebelumnya - DIPERBAIKI
private function hitungDenganDefault($bacaan_sekarang, $KONSTANTA_US, $KONSTANTA_TB)
{
    // Untuk data pertama, gunakan nilai langsung (bukan selisih)
    $A_sec = abs($bacaan_sekarang['US_GP']) * $KONSTANTA_US;
    $A_dalam_derajat = $A_sec * (1/3600);
    $A_dalam_radian = deg2rad($A_dalam_derajat);
    $sin_A_rad = sin($A_dalam_radian);
    
    // GUNAKAN KONSTANTA YANG SAMA: 1.14420
    $B_sec = abs($bacaan_sekarang['TB_GP']) * $KONSTANTA_TB;
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

    // Simpan hasil perhitungan BT2
    public function simpanPerhitunganBt2($data)
    {
        return $this->insert($data);
    }

    // Update hasil perhitungan BT2
    public function updatePerhitunganBt2($id_perhitungan, $data)
    {
        return $this->update($id_perhitungan, $data);
    }

    // Hapus hasil perhitungan BT2
    public function hapusPerhitunganBt2($id_perhitungan)
    {
        return $this->delete($id_perhitungan);
    }

    // Get semua perhitungan BT2
    public function getAllPerhitunganBt2()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }
}