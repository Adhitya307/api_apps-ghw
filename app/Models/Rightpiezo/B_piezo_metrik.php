<?php

namespace App\Models\Rightpiezo;

use CodeIgniter\Model;

class B_piezo_metrik extends Model
{
    protected $DBGroup          = 'db_right_piez';
    protected $table            = 'b_piezo_metrik';
    protected $primaryKey       = 'id_pengukuran';
    protected $useAutoIncrement = false;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_pengukuran', 'feet', 'inch', 'R-01', 'R-02', 'R-03', 'R-04', 'R-05', 'R-06', 
        'R-07', 'R-08', 'R-09', 'R-10', 'R-11', 'R-12', 'IPZ-01', 'PZ-04'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = '';
    protected $updatedField  = '';
    protected $deletedField  = '';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // ======================================================
    // METHOD PERHITUNGAN RUMUS RIGHT PIEZO
    // ======================================================

    /**
     * Rumus Excel: =IF(H57="KERING";$BC$11;((H57*$AK$10)+(I57*$AL$10)))
     * 
     * Keterangan variabel:
     * - H57 = feet dari tabel t_pembacaan (input user)
     * - I57 = inch dari tabel t_pembacaan (input user) 
     * - $BC$11 = kedalaman dari tabel i_reading_atas (nilai default)
     * - $AK$10 = feet konversi dari tabel b_piezo_metrik (0.3048)
     * - $AL$10 = inch konversi dari tabel b_piezo_metrik (0.0254)
     * 
     * @param string|float $feet_pembacaan    Nilai feet dari input user (bisa angka atau "KERING")
     * @param float $inch_pembacaan           Nilai inch dari input user
     * @param float $kedalaman                Nilai kedalaman dari i_reading_atas
     * @param float $feet_konversi            Nilai konversi feet (default: 0.3048)
     * @param float $inch_konversi            Nilai konversi inch (default: 0.0254)
     * @return float                          Hasil perhitungan
     */
    public function hitungRumusPiezo($feet_pembacaan, $inch_pembacaan, $kedalaman, $feet_konversi = 0.3048, $inch_konversi = 0.0254)
    {
        /**
         * LOGIKA RUMUS:
         * 1. Jika feet = "KERING" (case insensitive), maka hasil = kedalaman
         * 2. Jika tidak, maka hasil = (feet * feet_konversi) + (inch * inch_konversi)
         */
        
        // Cek jika feet = "KERING"
        if (is_string($feet_pembacaan) && strtoupper(trim($feet_pembacaan)) === 'KERING') {
            return floatval($kedalaman);
        }

        // Konversi nilai ke float (handle string angka)
        $feet = $this->convertToFloat($feet_pembacaan);
        $inch = $this->convertToFloat($inch_pembacaan);

        // Hitung rumus: ((feet * feet_konversi) + (inch * inch_konversi))
        $hasil = ($feet * $feet_konversi) + ($inch * $inch_konversi);
        
        // Bulatkan ke 6 desimal (sesuai format database)
        return round($hasil, 6);
    }

    /**
     * Hitung untuk semua lokasi sekaligus
     * 
     * @param int $pengukuran_id          ID pengukuran
     * @param array $data_pembacaan       Data pembacaan dari t_pembacaan [lokasi => [feet, inch]]
     * @param array $data_ireading        Data I-reading atas [lokasi => kedalaman]
     * @return array                      Hasil perhitungan per lokasi
     */
    public function hitungSemuaLokasi($pengukuran_id, $data_pembacaan, $data_ireading)
    {
        $hasilSemua = [];
        
        // Daftar semua lokasi Right Piezo
        $lokasiList = ['R-01', 'R-02', 'R-03', 'R-04', 'R-05', 'R-06', 
                      'R-07', 'R-08', 'R-09', 'R-10', 'R-11', 'R-12', 
                      'IPZ-01', 'PZ-04'];

        // Ambil nilai konversi dari database (default jika tidak ada)
        $metrik = $this->find($pengukuran_id);
        $feet_konversi = $metrik['feet'] ?? 0.3048;
        $inch_konversi = $metrik['inch'] ?? 0.0254;

        foreach ($lokasiList as $lokasi) {
            // Cek apakah data pembacaan dan I-reading tersedia untuk lokasi ini
            $feet_pembacaan = $data_pembacaan[$lokasi]['feet'] ?? 0;
            $inch_pembacaan = $data_pembacaan[$lokasi]['inch'] ?? 0;
            $kedalaman = $data_ireading[$lokasi] ?? 0;

            // Hitung menggunakan rumus
            $hasil = $this->hitungRumusPiezo($feet_pembacaan, $inch_pembacaan, $kedalaman, $feet_konversi, $inch_konversi);
            
            $hasilSemua[$lokasi] = [
                'hasil' => $hasil,
                'feet_input' => $feet_pembacaan,
                'inch_input' => $inch_pembacaan,
                'kedalaman' => $kedalaman,
                'rumus_terpakai' => $this->getRumusTerpakai($feet_pembacaan)
            ];
        }

        return $hasilSemua;
    }

    /**
     * Simpan hasil perhitungan ke database
     * 
     * @param int $pengukuran_id      ID pengukuran
     * @param array $hasilPerhitungan Hasil perhitungan [lokasi => nilai]
     * @return bool                   True jika berhasil
     */
    public function simpanHasilPerhitungan($pengukuran_id, $hasilPerhitungan)
    {
        try {
            // Data untuk update/insert
            $data = ['id_pengukuran' => $pengukuran_id];
            
            // Tambahkan hasil per lokasi
            foreach ($hasilPerhitungan as $lokasi => $nilai) {
                if (is_array($nilai)) {
                    $data[$lokasi] = $nilai['hasil'] ?? $nilai;
                } else {
                    $data[$lokasi] = $nilai;
                }
            }

            // Cek apakah data sudah ada
            $existing = $this->find($pengukuran_id);
            
            if ($existing) {
                // Update data existing
                return $this->update($pengukuran_id, $data);
            } else {
                // Tambahkan nilai default untuk feet dan inch
                $data['feet'] = 0.3048;
                $data['inch'] = 0.0254;
                return $this->insert($data);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error simpanHasilPerhitungan: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hitung dan simpan untuk satu lokasi spesifik
     * 
     * @param int $pengukuran_id      ID pengukuran
     * @param string $lokasi          Lokasi (R-01, R-02, ..., PZ-04)
     * @param float $feet_pembacaan   Nilai feet
     * @param float $inch_pembacaan   Nilai inch  
     * @param float $kedalaman        Nilai kedalaman
     * @return array                 Hasil perhitungan
     */
    public function hitungDanSimpanLokasi($pengukuran_id, $lokasi, $feet_pembacaan, $inch_pembacaan, $kedalaman)
    {
        // Ambil nilai konversi
        $metrik = $this->find($pengukuran_id);
        $feet_konversi = $metrik['feet'] ?? 0.3048;
        $inch_konversi = $metrik['inch'] ?? 0.0254;

        // Hitung rumus
        $hasil = $this->hitungRumusPiezo($feet_pembacaan, $inch_pembacaan, $kedalaman, $feet_konversi, $inch_konversi);

        // Simpan ke database
        $dataUpdate = [
            $lokasi => $hasil
        ];

        $existing = $this->find($pengukuran_id);
        if ($existing) {
            $this->update($pengukuran_id, $dataUpdate);
        } else {
            $dataInsert = [
                'id_pengukuran' => $pengukuran_id,
                'feet' => 0.3048,
                'inch' => 0.0254,
                $lokasi => $hasil
            ];
            $this->insert($dataInsert);
        }

        return [
            'status' => 'success',
            'lokasi' => $lokasi,
            'hasil' => $hasil,
            'feet_input' => $feet_pembacaan,
            'inch_input' => $inch_pembacaan,
            'kedalaman' => $kedalaman,
            'rumus_terpakai' => $this->getRumusTerpakai($feet_pembacaan)
        ];
    }

    /**
     * Helper method: Konversi nilai ke float
     */
    private function convertToFloat($value)
    {
        if (is_numeric($value)) {
            return floatval($value);
        }
        return 0.0;
    }

    /**
     * Helper method: Dapatkan string rumus yang terpakai
     */
    private function getRumusTerpakai($feet_pembacaan)
    {
        if (is_string($feet_pembacaan) && strtoupper(trim($feet_pembacaan)) === 'KERING') {
            return 'KEDALAMAN (karena KERING)';
        } else {
            return '(feet × 0.3048) + (inch × 0.0254)';
        }
    }

    /**
     * Validasi data sebelum perhitungan
     */
    public function validasiDataPerhitungan($pengukuran_id, $lokasi, $feet_pembacaan, $inch_pembacaan, $kedalaman)
    {
        $errors = [];

        if (empty($pengukuran_id)) {
            $errors[] = "ID pengukuran harus diisi";
        }

        if (empty($lokasi)) {
            $errors[] = "Lokasi harus diisi";
        }

        if ($feet_pembacaan === '' && $inch_pembacaan === '') {
            $errors[] = "Feet atau inch harus diisi";
        }

        if (empty($kedalaman)) {
            $errors[] = "Kedalaman harus diisi";
        }

        return $errors;
    }

    /**
     * Get default values untuk konversi
     */
    public function getDefaultKonversi()
    {
        return [
            'feet' => 0.3048,
            'inch' => 0.0254
        ];
    }
}