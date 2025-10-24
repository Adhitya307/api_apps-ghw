<?php
namespace App\Models\DomBody;

use CodeIgniter\Model;

class MPengukuranHdm extends Model
{
    protected $table = 't_pengukuran_hdm';
    protected $primaryKey = 'id_pengukuran';
    protected $allowedFields = [
        'tahun', 'periode', 'tanggal', 'dma', 'temp_id'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    // Tambahkan koneksi HDM
    protected $DBGroup = 'hdm';

    protected $validationRules = [
        'tahun' => 'required|numeric',
        'tanggal' => 'required|valid_date'
    ];

    protected $validationMessages = [
        'tahun' => [
            'required' => 'Tahun wajib diisi',
            'numeric' => 'Tahun harus berupa angka'
        ],
        'tanggal' => [
            'required' => 'Tanggal wajib diisi',
            'valid_date' => 'Format tanggal tidak valid'
        ]
    ];
}