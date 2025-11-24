<?php

namespace App\Models\Rightpiezo;

use CodeIgniter\Model;

class T_pengukuran_rightpiez extends Model
{
    protected $DBGroup          = 'db_right_piez';
    protected $table            = 't_pengukuran_rightpiez';
    protected $primaryKey       = 'id_pengukuran';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['tahun', 'tanggal', 'periode', 'tma', 'ch_hujan', 'temp_id'];

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
}