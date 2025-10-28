<?php
namespace App\Models\DomBody;

use CodeIgniter\Model;

class AmbangBatas625H1Model extends Model
{
    protected $DBGroup = 'hdm';
    protected $table = 'ambang_batas_625_h1';
    protected $primaryKey = 'id_ambang_batas';
    protected $allowedFields = [
        'id_pengukuran', 
        'aman', 
        'peringatan', 
        'bahaya', 
        'pergerakan',
        'created_at', 
        'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get data by id_pengukuran
     */
    public function getByPengukuran($pengukuran_id)
    {
        return $this->where('id_pengukuran', $pengukuran_id)->first();
    }

    /**
     * Insert data default
     */
    public function insertDefault($pengukuran_id)
    {
        return $this->insert([
            'id_pengukuran' => $pengukuran_id,
            'aman' => -18.77,
            'peringatan' => -21.66,
            'bahaya' => -25.60,
            'pergerakan' => null
        ]);
    }

    /**
     * Update data by id_pengukuran
     */
    public function updateByPengukuran($pengukuran_id, $data)
    {
        return $this->where('id_pengukuran', $pengukuran_id)->set($data)->update();
    }

    /**
     * Delete data by id_pengukuran
     */
    public function deleteByPengukuran($pengukuran_id)
    {
        return $this->where('id_pengukuran', $pengukuran_id)->delete();
    }

    /**
     * Update pergerakan from t_pergerakan_elv625 hv_1 * 10
     */
    public function updatePergerakanFromHv1($pengukuran_id)
    {
        $pergerakanModel = new MPergerakanElv625();
        $pergerakan = $pergerakanModel->getByPengukuran($pengukuran_id);
        
        if ($pergerakan && isset($pergerakan['hv_1'])) {
            $pergerakan_value = $pergerakan['hv_1'] * 10;
            return $this->where('id_pengukuran', $pengukuran_id)
                        ->set('pergerakan', $pergerakan_value)
                        ->update();
        }
        
        return false;
    }
}