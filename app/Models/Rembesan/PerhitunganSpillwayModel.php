<?php
namespace App\Models\Rembesan;

use CodeIgniter\Model;

class PerhitunganSpillwayModel extends Model
{
    protected $table = 'p_spillway';
    protected $primaryKey = 'id';
    protected $allowedFields = ['pengukuran_id', 'b3', 'ambang', 'created_at', 'updated_at'];
    
    protected $validationRules = [
        'pengukuran_id' => 'required|numeric|is_not_unique[t_data_pengukuran.id]',
        'b3' => 'permit_empty|numeric',
        'ambang' => 'permit_empty|numeric'
    ];
    
    protected $validationMessages = [
        'pengukuran_id' => [
            'required' => 'pengukuran_id harus diisi',
            'numeric' => 'pengukuran_id harus berupa angka',
            'is_not_unique' => 'Data pengukuran dengan ID {value} tidak ditemukan'
        ]
    ];
    
    protected $useTimestamps = true;
}
