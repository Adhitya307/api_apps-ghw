<?php

namespace App\Models\Rightpiezo;

use CodeIgniter\Model;

class Elevasi_dasar extends Model
{
    protected $DBGroup          = 'db_right_piez';
    protected $table            = 'elevasi_dasar';
    protected $primaryKey       = 'id_pengukuran';
    protected $useAutoIncrement = false;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_pengukuran',
        // R-01
        'R-01_elv_piez', 'R-01_kedalaman', 'R-01_data',
        // R-02
        'R-02_elv_piez', 'R-02_kedalaman', 'R-02_data',
        // R-03
        'R-03_elv_piez', 'R-03_kedalaman', 'R-03_data',
        // R-04
        'R-04_elv_piez', 'R-04_kedalaman', 'R-04_data',
        // R-05
        'R-05_elv_piez', 'R-05_kedalaman', 'R-05_data',
        // R-06
        'R-06_elv_piez', 'R-06_kedalaman', 'R-06_data',
        // R-07
        'R-07_elv_piez', 'R-07_kedalaman', 'R-07_data',
        // R-08
        'R-08_elv_piez', 'R-08_kedalaman', 'R-08_data',
        // R-09
        'R-09_elv_piez', 'R-09_kedalaman', 'R-09_data',
        // R-10
        'R-10_elv_piez', 'R-10_kedalaman', 'R-10_data',
        // R-11
        'R-11_elv_piez', 'R-11_kedalaman', 'R-11_data',
        // R-12
        'R-12_elv_piez', 'R-12_kedalaman', 'R-12_data',
        // IPZ-01
        'IPZ-01_elv_piez', 'IPZ-01_kedalaman', 'IPZ-01_data',
        // PZ-04
        'PZ-04_elv_piez', 'PZ-04_kedalaman', 'PZ-04_data'
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
}