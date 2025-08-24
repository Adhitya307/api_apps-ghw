<?php
namespace App\Models\Rembesan;

use CodeIgniter\Model;

class PerhitunganIntiGaleryModel extends Model
{
    protected $table      = 'p_intigalery';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'pengukuran_id',
        'a1',
        'ambang_a1',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';

    protected $validationRules = [
        'pengukuran_id' => 'required|numeric|is_not_unique[t_data_pengukuran.id]',
        'a1'            => 'permit_empty|numeric',
        'ambang_a1'     => 'permit_empty|numeric'
    ];

    protected $validationMessages = [
        'pengukuran_id' => [
            'required'     => 'pengukuran_id harus diisi',
            'numeric'      => 'pengukuran_id harus berupa angka',
            'is_not_unique'=> 'Data pengukuran dengan ID {value} tidak ditemukan'
        ]
    ];
}
