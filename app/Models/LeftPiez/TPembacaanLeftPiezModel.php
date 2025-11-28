<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;

class TPembacaanLeftPiezModel extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 't_pembacaan_left_piez';
    protected $primaryKey = 'id_pembacaan';
    protected $allowedFields = [
        'id_pengukuran', 
        'tipe_piezometer', 
        'feet', 
        'inch'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'feet' => 'permit_empty',
        'inch' => 'permit_empty|decimal'
    ];

    // Daftar semua tipe piezometer yang valid
    protected $validPiezometers = [
        'L01', 'L02', 'L03', 'L04', 'L05', 
        'L06', 'L07', 'L08', 'L09', 'L10', 'SPZ02'
    ];

    /**
     * Get data pembacaan by pengukuran dan tipe
     */
    public function getByPengukuranDanTipe($id_pengukuran, $tipePiezometer)
    {
        return $this->where('id_pengukuran', $id_pengukuran)
                   ->where('tipe_piezometer', $tipePiezometer)
                   ->first();
    }

    /**
     * Get semua data pembacaan untuk satu pengukuran
     */
    public function getByPengukuran($id_pengukuran)
    {
        return $this->where('id_pengukuran', $id_pengukuran)->findAll();
    }

    /**
     * Insert atau update data pembacaan
     */
    public function simpanPembacaan($id_pengukuran, $tipePiezometer, $data)
    {
        // Validasi tipe piezometer
        $tipePiezometer = strtoupper($tipePiezometer);
        if (!in_array($tipePiezometer, $this->validPiezometers)) {
            throw new \Exception('Tipe piezometer tidak valid: ' . $tipePiezometer);
        }

        // Cek apakah data sudah ada
        $existing = $this->getByPengukuranDanTipe($id_pengukuran, $tipePiezometer);
        
        if ($existing) {
            // Update data yang sudah ada
            return $this->update($existing['id_pembacaan'], $data);
        } else {
            // Insert data baru
            $data['id_pengukuran'] = $id_pengukuran;
            $data['tipe_piezometer'] = $tipePiezometer;
            return $this->insert($data);
        }
    }

    /**
     * Get data pembacaan untuk semua tipe dalam satu pengukuran
     */
    public function getSemuaByPengukuran($id_pengukuran)
    {
        $results = [];
        foreach ($this->validPiezometers as $tipe) {
            $data = $this->getByPengukuranDanTipe($id_pengukuran, $tipe);
            $results[$tipe] = $data;
        }
        return $results;
    }

    /**
     * Validasi tipe piezometer
     */
    public function isValidPiezometer($tipePiezometer)
    {
        return in_array(strtoupper($tipePiezometer), $this->validPiezometers);
    }
}