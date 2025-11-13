<?php

namespace App\Models\Exstenso;

use CodeIgniter\Model;

class DeformasiEx3Model extends Model
{
    protected $DBGroup          = 'db_exs';
    protected $table            = 'p_deformasi_Ex3';
    protected $primaryKey       = 'id_deformasi_ex3';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_pengukuran', 'deformasi_10', 'deformasi_20', 'deformasi_30', 'pemb_awal10', 'pemb_awal20', 'pemb_awal30'];

    protected $useTimestamps = false;

    /**
     * Hitung deformasi untuk Ex3
     * Rumus: deformasi = pembacaan - pemb_awal
     */
    public function hitungDeformasi($idPengukuran, $pembacaan10, $pembacaan20, $pembacaan30)
    {
        // Nilai default pemb_awal untuk Ex3
        $pemb_awal10 = 37.75;
        $pemb_awal20 = 39.15;
        $pemb_awal30 = 41.40;

        // Hitung deformasi berdasarkan rumus
        $deformasi10 = $pembacaan10 - $pemb_awal10;
        $deformasi20 = $pembacaan20 - $pemb_awal20;
        $deformasi30 = $pembacaan30 - $pemb_awal30;

        // Data untuk disimpan
        $data = [
            'id_pengukuran' => $idPengukuran,
            'pemb_awal10'   => $pemb_awal10,
            'pemb_awal20'   => $pemb_awal20,
            'pemb_awal30'   => $pemb_awal30,
            'deformasi_10'  => $deformasi10,
            'deformasi_20'  => $deformasi20,
            'deformasi_30'  => $deformasi30
        ];

        // Cek apakah data sudah ada
        $existingData = $this->where('id_pengukuran', $idPengukuran)->first();
        
        if ($existingData) {
            // Update data yang sudah ada
            return $this->update($existingData['id_deformasi_ex3'], $data);
        } else {
            // Insert data baru
            return $this->insert($data);
        }
    }

    /**
     * Hitung deformasi dari data PembacaanEx3Model
     */
    public function hitungDeformasiFromPembacaan($idPengukuran, $pembacaanModel)
    {
        // Ambil data pembacaan dari tabel t_pembacaan_ex3
        $pembacaan = $pembacaanModel->where('id_pengukuran', $idPengukuran)->first();
        
        if (!$pembacaan) {
            throw new \Exception('Data pembacaan Ex3 tidak ditemukan untuk id_pengukuran: ' . $idPengukuran);
        }

        return $this->hitungDeformasi(
            $idPengukuran,
            $pembacaan['pembacaan_10'],
            $pembacaan['pembacaan_20'],
            $pembacaan['pembacaan_30']
        );
    }
}