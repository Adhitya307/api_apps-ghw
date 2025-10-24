<?php
namespace App\Models\DomBody;

use CodeIgniter\Model;

class MPembacaanElv600 extends Model
{
    protected $table = 't_pembacaan_hdm_elv600';
    protected $primaryKey = 'id_pembacaan';
    protected $allowedFields = [
        'id_pengukuran', 'hv_1', 'hv_2', 'hv_3', 'hv_4', 'hv_5'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    // Tambahkan koneksi HDM
    protected $DBGroup = 'hdm';

    protected $validationRules = [
        'id_pengukuran' => 'required|numeric'
    ];

    protected $validationMessages = [
        'id_pengukuran' => [
            'required' => 'ID Pengukuran wajib diisi',
            'numeric' => 'ID Pengukuran harus berupa angka'
        ]
    ];
}