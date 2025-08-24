<?php
namespace App\Models\Rembesan;

use CodeIgniter\Model;

class TebingKananModel extends Model
{
    protected $table      = 'p_tebingkanan';
    protected $primaryKey = 'id';
    protected $allowedFields = ['pengukuran_id', 'sr', 'ambang', 'B5', 'created_at', 'updated_at'];
    
    protected $validationRules = [
        'pengukuran_id' => 'required|numeric|is_not_unique[t_data_pengukuran.id]',
        'sr'            => 'permit_empty|numeric',
        'ambang'        => 'permit_empty|numeric',
        'B5'            => 'permit_empty|numeric'
    ];
    
    protected $validationMessages = [
        'pengukuran_id' => [
            'required'       => 'pengukuran_id harus diisi',
            'numeric'        => 'pengukuran_id harus berupa angka',
            'is_not_unique'  => 'Data pengukuran dengan ID {value} tidak ditemukan'
        ]
    ];
    
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
