<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;

class IreadingA extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'i_reading_A_all'; // Hanya 1 tabel
    protected $primaryKey = 'id_reading_A';
    protected $allowedFields = ['id_pengukuran', 'titik_piezometer', 'Elv_Piez'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
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
        foreach ($readings as $titik => $elevasi) {
            $data[] = [
                'id_pengukuran' => $idPengukuran,
                'titik_piezometer' => $titik,
                'Elv_Piez' => $elevasi
            ];
        }
        
        return $this->insertBatch($data);
    }
    
    /**
     * Convenience methods for specific points
     */
    public function forAL01($idPengukuran = null) { return $this->getByTitik('L_01', $idPengukuran); }
    public function forAL02($idPengukuran = null) { return $this->getByTitik('L_02', $idPengukuran); }
    public function forAL03($idPengukuran = null) { return $this->getByTitik('L_03', $idPengukuran); }
    public function forAL04($idPengukuran = null) { return $this->getByTitik('L_04', $idPengukuran); }
    public function forAL05($idPengukuran = null) { return $this->getByTitik('L_05', $idPengukuran); }
    public function forAL06($idPengukuran = null) { return $this->getByTitik('L_06', $idPengukuran); }
    public function forAL07($idPengukuran = null) { return $this->getByTitik('L_07', $idPengukuran); }
    public function forAL08($idPengukuran = null) { return $this->getByTitik('L_08', $idPengukuran); }
    public function forAL09($idPengukuran = null) { return $this->getByTitik('L_09', $idPengukuran); }
    public function forAL10($idPengukuran = null) { return $this->getByTitik('L_10', $idPengukuran); }
    public function forASpz02($idPengukuran = null) { return $this->getByTitik('SPZ_02', $idPengukuran); }
}