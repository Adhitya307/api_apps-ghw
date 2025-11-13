<?php

namespace App\Models\Exstenso;

use CodeIgniter\Model;

class DeformasiEx1Model extends Model
{
    protected $DBGroup          = 'db_exs';
    protected $table            = 'p_deformasi_Ex1';
    protected $primaryKey       = 'id_deformasi_ex1';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_pengukuran', 'deformasi_10', 'deformasi_20', 'deformasi_30', 'pemb_awal10', 'pemb_awal20', 'pemb_awal30'];

    protected $useTimestamps = false;

    /**
     * Hitung deformasi berdasarkan data pembacaan
     */
    public function hitungDeformasi($idPengukuran, $pembacaan10, $pembacaan20, $pembacaan30)
    {
        // Cek apakah sudah ada data deformasi untuk id_pengukuran ini
        $existingData = $this->where('id_pengukuran', $idPengukuran)->first();
        
        if ($existingData) {
            // Gunakan nilai pemb_awal yang sudah ada
            $pembAwal10 = $existingData['pemb_awal10'] ?? 35.00;
            $pembAwal20 = $existingData['pemb_awal20'] ?? 40.95;
            $pembAwal30 = $existingData['pemb_awal30'] ?? 29.80;
        } else {
            // Gunakan nilai default untuk pemb_awal
            $pembAwal10 = 35.00;
            $pembAwal20 = 40.95;
            $pembAwal30 = 29.80;
        }

        // Hitung deformasi berdasarkan rumus: pembacaan - pemb_awal
        $deformasi10 = $pembacaan10 - $pembAwal10;
        $deformasi20 = $pembacaan20 - $pembAwal20;
        $deformasi30 = $pembacaan30 - $pembAwal30;

        // Siapkan data untuk disimpan
        $data = [
            'id_pengukuran' => $idPengukuran,
            'deformasi_10' => $deformasi10,
            'deformasi_20' => $deformasi20,
            'deformasi_30' => $deformasi30,
            'pemb_awal10' => $pembAwal10,
            'pemb_awal20' => $pembAwal20,
            'pemb_awal30' => $pembAwal30
        ];

        if ($existingData) {
            // Update data yang sudah ada
            return $this->update($existingData['id_deformasi_ex1'], $data);
        } else {
            // Insert data baru
            return $this->insert($data);
        }
    }

    /**
     * Hitung deformasi dari data PembacaanEx1Model
     */
    public function hitungDeformasiFromPembacaan($idPengukuran, PembacaanEx1Model $pembacaanModel)
    {
        // Ambil data pembacaan berdasarkan id_pengukuran
        $pembacaan = $pembacaanModel->where('id_pengukuran', $idPengukuran)->first();
        
        if (!$pembacaan) {
            throw new \Exception('Data pembacaan tidak ditemukan untuk id_pengukuran: ' . $idPengukuran);
        }

        return $this->hitungDeformasi(
            $idPengukuran,
            $pembacaan['pembacaan_10'],
            $pembacaan['pembacaan_20'],
            $pembacaan['pembacaan_30']
        );
    }
}
?>