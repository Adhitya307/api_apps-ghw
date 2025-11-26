<?php

namespace App\Models\Rightpiezo;

use CodeIgniter\Model;

class Perhitungan_t_psmetrik extends Model
{
    protected $DBGroup          = 'db_right_piez';
    protected $table            = 'perhitungan_t_psmetrik';
    protected $primaryKey       = 'id_pengukuran';
    protected $useAutoIncrement = false;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_pengukuran', 
        'R-01', 'R-02', 'R-03', 'R-04', 'R-05', 'R-06', 
        'R-07', 'R-08', 'R-09', 'R-10', 'R-11', 'R-12', 
        'IPZ-01', 'PZ-04'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
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

    /**
     * Hitung rumus final: Elv_Piez - Hasil_B_Piezo_Metrik
     * 
     * @param float $elv_piez        Nilai Elv_Piez dari i_reading_atas
     * @param float $hasil_b_metrik  Nilai hasil dari B_piezo_metrik
     * @return float                 Hasil perhitungan final
     */
    public function hitungRumusFinal($elv_piez, $hasil_b_metrik)
    {
        return $elv_piez - $hasil_b_metrik;
    }

    /**
     * Hitung semua lokasi sekaligus dengan rumus final
     * 
     * @param int $pengukuran_id          ID pengukuran
     * @param array $data_elv_piez        Data Elv_Piez dari i_reading_atas [lokasi => elv_piez]
     * @param array $data_b_metrik        Data hasil dari B_piezo_metrik [lokasi => hasil]
     * @return array                      Hasil perhitungan final per lokasi
     */
    public function hitungSemuaLokasi($pengukuran_id, $data_elv_piez, $data_b_metrik)
    {
        $hasilSemua = [];
        
        // Daftar semua lokasi Right Piezo
        $lokasiList = ['R-01', 'R-02', 'R-03', 'R-04', 'R-05', 'R-06', 
                      'R-07', 'R-08', 'R-09', 'R-10', 'R-11', 'R-12', 
                      'IPZ-01', 'PZ-04'];

        foreach ($lokasiList as $lokasi) {
            // Ambil nilai Elv_Piez dan hasil B_metrik
            $elv_piez = $data_elv_piez[$lokasi] ?? 0;
            $hasil_b_metrik = $data_b_metrik[$lokasi] ?? 0;

            // Hitung rumus final: Elv_Piez - Hasil_B_Metrik
            $hasil_final = $this->hitungRumusFinal($elv_piez, $hasil_b_metrik);
            
            $hasilSemua[$lokasi] = [
                'hasil' => $hasil_final,
                'elv_piez' => $elv_piez,
                'hasil_b_metrik' => $hasil_b_metrik,
                'rumus' => "Elv_Piez - Hasil_B_Metrik"
            ];
        }

        return $hasilSemua;
    }

    /**
     * Hitung dan simpan untuk satu lokasi spesifik
     * 
     * @param int $pengukuran_id      ID pengukuran
     * @param string $lokasi          Lokasi (R-01, R-02, ..., PZ-04)
     * @param float $elv_piez         Nilai Elv_Piez dari i_reading_atas
     * @param float $hasil_b_metrik   Nilai hasil dari B_piezo_metrik
     * @return array                 Hasil perhitungan
     */
    public function hitungDanSimpanLokasi($pengukuran_id, $lokasi, $elv_piez, $hasil_b_metrik)
    {
        // Hitung rumus final
        $hasil_final = $this->hitungRumusFinal($elv_piez, $hasil_b_metrik);

        // Simpan ke database
        $dataUpdate = [
            $lokasi => $hasil_final
        ];

        $existing = $this->find($pengukuran_id);
        if ($existing) {
            $this->update($pengukuran_id, $dataUpdate);
        } else {
            $dataInsert = [
                'id_pengukuran' => $pengukuran_id,
                $lokasi => $hasil_final
            ];
            $this->insert($dataInsert);
        }

        return [
            'status' => 'success',
            'lokasi' => $lokasi,
            'hasil_final' => $hasil_final,
            'elv_piez' => $elv_piez,
            'hasil_b_metrik' => $hasil_b_metrik,
            'rumus' => "Elv_Piez - Hasil_B_Metrik"
        ];
    }

    /**
     * Simpan hasil perhitungan ke tabel ini
     * 
     * @param int $pengukuran_id
     * @param array $hasilPerhitungan [lokasi => nilai]
     * @return bool
     */
    public function simpanHasilPerhitungan($pengukuran_id, $hasilPerhitungan)
    {
        try {
            $data = ['id_pengukuran' => $pengukuran_id];
            
            // Map hasil perhitungan ke kolom yang sesuai
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
                return $this->update($pengukuran_id, $data);
            } else {
                return $this->insert($data);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error simpanHasilPerhitungan T.PsMetrik: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Proses lengkap perhitungan untuk semua lokasi
     * 
     * @param int $pengukuran_id ID pengukuran
     * @return array|bool Hasil perhitungan atau false jika gagal
     */
    public function prosesPerhitunganLengkap($pengukuran_id)
    {
        try {
            // 1. Ambil data Elv_Piez dari i_reading_atas
            $modelIReading = new \App\Models\Rightpiezo\I_reading_atas();
            $data_elv_piez = $modelIReading->getElvPiezByPengukuran($pengukuran_id);

            // 2. Ambil data hasil dari B_piezo_metrik
            $modelBMetrik = new \App\Models\Rightpiezo\B_piezo_metrik();
            $data_b_metrik = $modelBMetrik->getHasilByPengukuran($pengukuran_id);

            // 3. Hitung rumus final untuk semua lokasi
            $hasil_final = $this->hitungSemuaLokasi($pengukuran_id, $data_elv_piez, $data_b_metrik);

            // 4. Simpan hasil final
            $simpan = $this->simpanHasilPerhitungan($pengukuran_id, $hasil_final);

            return $simpan ? $hasil_final : false;

        } catch (\Exception $e) {
            log_message('error', 'Error prosesPerhitunganLengkap: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil hasil perhitungan berdasarkan pengukuran_id
     * 
     * @param int $pengukuran_id
     * @return array|null
     */
    public function getHasilByPengukuran($pengukuran_id)
    {
        return $this->find($pengukuran_id);
    }

    /**
     * Ambil semua hasil perhitungan dengan join ke tabel pengukuran
     * 
     * @return array
     */
    public function getAllWithPengukuran()
    {
        return $this->db->table($this->table . ' pt')
            ->select('pt.*, tpr.tahun, tpr.periode, tpr.tanggal, tpr.tma, tpr.ch_hujan')
            ->join('t_pengukuran_rightpiez tpr', 'pt.id_pengukuran = tpr.id_pengukuran')
            ->orderBy('tpr.tanggal', 'DESC')
            ->orderBy('tpr.tahun', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Hapus hasil perhitungan berdasarkan pengukuran_id
     * 
     * @param int $pengukuran_id
     * @return bool
     */
    public function hapusHasil($pengukuran_id)
    {
        return $this->delete($pengukuran_id);
    }

    /**
     * Cek apakah sudah ada perhitungan untuk pengukuran_id
     * 
     * @param int $pengukuran_id
     * @return bool
     */
    public function sudahDihitung($pengukuran_id)
    {
        $data = $this->find($pengukuran_id);
        return !empty($data);
    }

    /**
     * Validasi data sebelum perhitungan
     */
    public function validasiDataPerhitungan($pengukuran_id, $lokasi, $elv_piez, $hasil_b_metrik)
    {
        $errors = [];

        if (empty($pengukuran_id)) {
            $errors[] = "ID pengukuran harus diisi";
        }

        if (empty($lokasi)) {
            $errors[] = "Lokasi harus diisi";
        }

        if (empty($elv_piez)) {
            $errors[] = "Elv_Piez harus diisi";
        }

        if ($hasil_b_metrik === '') {
            $errors[] = "Hasil B Metrik harus diisi";
        }

        return $errors;
    }
}