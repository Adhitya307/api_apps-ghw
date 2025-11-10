<?php

namespace App\Models\Btm;

use CodeIgniter\Model;

class PengukuranBtmModel extends Model
{
    protected $DBGroup          = 'btm';
    protected $table            = 't_pengukuran_btm';
    protected $primaryKey       = 'id_pengukuran';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['tahun', 'periode', 'tanggal', 'temp_id'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Get all pengukuran dengan urutan yang benar
    public function getAllPengukuran()
    {
        return $this->orderBy('tanggal', 'ASC')
                    ->orderBy('tahun', 'ASC')
                    ->orderBy('periode', 'ASC')
                    ->findAll();
    }

    // Get pengukuran sebelumnya berdasarkan tanggal
    public function getPengukuranSebelumnya($tanggal)
    {
        return $this->where('tanggal <', $tanggal)
                    ->orderBy('tanggal', 'DESC')
                    ->first();
    }
}