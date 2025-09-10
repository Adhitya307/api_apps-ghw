<?php

namespace App\Models\Rembesan;

use CodeIgniter\Model;

class AnalisaLookBurtModel extends Model
{
    protected $table            = 'analisa_look_burt';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'pengukuran_id',
        'rembesan_bendungan',
        'panjang_bendungan',
        'rembesan_per_m',
        'nilai_ambang_ok',
        'nilai_ambang_notok',
        'keterangan'
    ];

    // Ambil data dengan join ke t_data_pengukuran
public function getAll()
{
    return $this->select('analisa_look_burt.*, t_data_pengukuran.tanggal, t_data_pengukuran.tma_waduk')
                ->join('t_data_pengukuran', 't_data_pengukuran.id = analisa_look_burt.pengukuran_id', 'left')
                ->orderBy('t_data_pengukuran.tanggal', 'ASC') // urut dari tanggal terkecil
                ->findAll();
}

}
