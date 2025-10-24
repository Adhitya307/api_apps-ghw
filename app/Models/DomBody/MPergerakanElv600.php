<?php
namespace App\Models\DomBody;

use CodeIgniter\Model;

class MPergerakanElv600 extends Model
{
    protected $DBGroup = 'hdm';
    protected $table = 't_pergerakan_elv600';
    protected $primaryKey = 'id_pergerakan';
    protected $allowedFields = [
        'id_pengukuran', 
        'hv_1', 
        'hv_2', 
        'hv_3', 
        'hv_4', 
        'hv_5', 
        'created_at', 
        'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * 🔹 Hitung dan simpan pergerakan berdasarkan id_pengukuran
     */
    public function hitungPergerakan($id_pengukuran)
    {
        // Load model pembacaan dan initial reading
        $pembacaanModel = new \App\Models\DomBody\MPembacaanElv600();
        $initialModel = new \App\Models\DomBody\InitialReadingElv600Model();
        
        // Ambil data pembacaan terakhir
        $pembacaan = $pembacaanModel->where('id_pengukuran', $id_pengukuran)
                                    ->orderBy('id_pembacaan', 'DESC')
                                    ->first();

        // Ambil data initial reading
        $initial = $initialModel->where('id_pengukuran', $id_pengukuran)->first();
        
        if (!$pembacaan || !$initial) {
            return [
                'status' => 'error',
                'message' => 'Data pembacaan atau initial reading tidak ditemukan!'
            ];
        }
        
        // Hitung pergerakan (pembacaan - initial)
        $hasil = [
            'id_pengukuran' => $id_pengukuran,
            'hv_1' => $pembacaan['hv_1'] - $initial['hv_1'],
            'hv_2' => $pembacaan['hv_2'] - $initial['hv_2'],
            'hv_3' => $pembacaan['hv_3'] - $initial['hv_3'],
            'hv_4' => $pembacaan['hv_4'] - $initial['hv_4'],
            'hv_5' => $pembacaan['hv_5'] - $initial['hv_5']
        ];

        // Cek apakah pergerakan sudah ada
        $existing = $this->where('id_pengukuran', $id_pengukuran)->first();

        if ($existing) {
            $this->update($existing['id_pergerakan'], $hasil);
            $hasil['mode'] = 'update';
        } else {
            $this->insert($hasil);
            $hasil['mode'] = 'insert';
        }

        return [
            'status' => 'success',
            'message' => 'Pergerakan ELV600 berhasil dihitung dan disimpan.',
            'data' => $hasil
        ];
    }

    /**
     * 🔹 Hitung semua pergerakan (loop semua pengukuran)
     */
    public function hitungSemuaPergerakan()
    {
        $pengukuranModel = new \App\Models\DomBody\MPengukuranHdm();
        $pengukurans = $pengukuranModel->findAll();
        
        $results = [];
        foreach ($pengukurans as $pengukuran) {
            $results[] = [
                'id_pengukuran' => $pengukuran['id_pengukuran'],
                'hasil' => $this->hitungPergerakan($pengukuran['id_pengukuran'])
            ];
        }
        
        return $results;
    }

    /**
     * Get data pergerakan berdasarkan id_pengukuran
     */
    public function getByPengukuran($pengukuran_id)
    {
        return $this->where('id_pengukuran', $pengukuran_id)->first();
    }

    /**
     * Update data berdasarkan id_pengukuran
     */
    public function updateByPengukuran($pengukuran_id, $data)
    {
        return $this->where('id_pengukuran', $pengukuran_id)->set($data)->update();
    }

    /**
     * Hapus data pergerakan berdasarkan id_pengukuran
     */
    public function deleteByPengukuran($pengukuran_id)
    {
        return $this->where('id_pengukuran', $pengukuran_id)->delete();
    }

    /**
     * Ambil semua data pergerakan
     */
    public function getAll()
    {
        return $this->findAll();
    }
}
