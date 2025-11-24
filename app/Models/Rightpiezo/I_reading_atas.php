<?php

namespace App\Models\Rightpiezo;

use CodeIgniter\Model;

class I_reading_atas extends Model
{
    protected $DBGroup          = 'db_right_piez';
    protected $table            = 'i_reading_atas';
    protected $primaryKey       = 'id_reading_atas';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_pengukuran', 'titik_piezometer', 'Elv_Piez', 'kedalaman'];
    
    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
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
    
    /**
     * Get data by titik piezometer
     */
    public function getByTitik($titik, $idPengukuran = null)
    {
        $builder = $this->where('titik_piezometer', $titik);
        
        if ($idPengukuran) {
            $builder->where('id_pengukuran', $idPengukuran);
        }
        
        return $builder->findAll();
    }
    
    /**
     * Get all data for specific pengukuran
     */
    public function getByPengukuran($idPengukuran)
    {
        return $this->where('id_pengukuran', $idPengukuran)
                   ->orderBy('titik_piezometer', 'ASC')
                   ->findAll();
    }
    
    /**
     * Insert multiple readings at once
     */
    public function insertInitialReadings($idPengukuran, array $readings)
    {
        $data = [];
        foreach ($readings as $titik => $reading) {
            $data[] = [
                'id_pengukuran' => $idPengukuran,
                'titik_piezometer' => $titik,
                'Elv_Piez' => $reading['elv_piez'],
                'kedalaman' => $reading['kedalaman']
            ];
        }
        
        return $this->insertBatch($data);
    }
    
    /**
     * Convenience methods for specific points
     */
    public function forR01($idPengukuran = null) { return $this->getByTitik('R-01', $idPengukuran); }
    public function forR02($idPengukuran = null) { return $this->getByTitik('R-02', $idPengukuran); }
    public function forR03($idPengukuran = null) { return $this->getByTitik('R-03', $idPengukuran); }
    public function forR04($idPengukuran = null) { return $this->getByTitik('R-04', $idPengukuran); }
    public function forR05($idPengukuran = null) { return $this->getByTitik('R-05', $idPengukuran); }
    public function forR06($idPengukuran = null) { return $this->getByTitik('R-06', $idPengukuran); }
    public function forR07($idPengukuran = null) { return $this->getByTitik('R-07', $idPengukuran); }
    public function forR08($idPengukuran = null) { return $this->getByTitik('R-08', $idPengukuran); }
    public function forR09($idPengukuran = null) { return $this->getByTitik('R-09', $idPengukuran); }
    public function forR10($idPengukuran = null) { return $this->getByTitik('R-10', $idPengukuran); }
    public function forR11($idPengukuran = null) { return $this->getByTitik('R-11', $idPengukuran); }
    public function forR12($idPengukuran = null) { return $this->getByTitik('R-12', $idPengukuran); }
    public function forIPZ01($idPengukuran = null) { return $this->getByTitik('IPZ-01', $idPengukuran); }
    public function forPZ04($idPengukuran = null) { return $this->getByTitik('PZ-04', $idPengukuran); }
}