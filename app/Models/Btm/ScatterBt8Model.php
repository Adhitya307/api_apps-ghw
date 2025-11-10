<?php

namespace App\Models\Btm;

use CodeIgniter\Model;

class ScatterBt8Model extends Model
{
    protected $DBGroup          = 'btm';
    protected $table            = 'p_scatter_bt_8';
    protected $primaryKey       = 'id_scatter';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_pengukuran', 'Y_US', 'X_TB', 'Y_cum', 'X_cum'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Hitung dan simpan data scatter untuk BT-8
     */
    public function calculateScatterData($idPengukuran, $aSec, $bSec, $usArah, $tbArah)
    {
        // Hitung Y_US berdasarkan rumus: IF(US_Arah="U";A_sec;(-A_sec))
        $Y_US = ($usArah == 'U') ? $aSec : (-$aSec);
        
        // Hitung X_TB berdasarkan rumus: IF(TB_Arah="T";B_sec;(-B_sec))
        $X_TB = ($tbArah == 'T') ? $bSec : (-$bSec);
        
        // Ambil data scatter sebelumnya untuk kumulatif
        $previousScatter = $this->getPreviousScatterData($idPengukuran);
        
        // Hitung Y_cum dan X_cum
        $previous_Y_cum = $previousScatter ? $previousScatter['Y_cum'] : 0;
        $previous_X_cum = $previousScatter ? $previousScatter['X_cum'] : 0;
        
        $Y_cum = $previous_Y_cum + $Y_US;
        $X_cum = $previous_X_cum + $X_TB;
        
        // Cek apakah data sudah ada
        $existingData = $this->where('id_pengukuran', $idPengukuran)->first();
        
        if ($existingData) {
            // Update data yang sudah ada
            return $this->update($existingData['id_scatter'], [
                'Y_US' => $Y_US,
                'X_TB' => $X_TB,
                'Y_cum' => $Y_cum,
                'X_cum' => $X_cum
            ]);
        } else {
            // Simpan data scatter baru
            $scatterData = [
                'id_pengukuran' => $idPengukuran,
                'Y_US' => $Y_US,
                'X_TB' => $X_TB,
                'Y_cum' => $Y_cum,
                'X_cum' => $X_cum
            ];
            
            return $this->insert($scatterData);
        }
    }
    
    /**
     * Ambil data scatter sebelumnya untuk kumulatif
     */
    private function getPreviousScatterData($idPengukuran)
    {
        return $this->select('p_scatter_bt_8.*')
                   ->join('t_pengukuran_btm t', 't.id_pengukuran = p_scatter_bt_8.id_pengukuran')
                   ->where('p_scatter_bt_8.id_pengukuran <', $idPengukuran)
                   ->orderBy('t.tahun ASC, t.periode ASC, t.tanggal ASC, p_scatter_bt_8.id_pengukuran ASC')
                   ->first();
    }
    
    /**
     * Hitung ulang semua data kumulatif
     */
    public function recalculateAllCumulative()
    {
        // Ambil semua data scatter urut berdasarkan pengukuran
        $allScatterData = $this->select('p_scatter_bt_8.*, t.tahun, t.periode, t.tanggal')
                              ->join('t_pengukuran_btm t', 't.id_pengukuran = p_scatter_bt_8.id_pengukuran')
                              ->orderBy('t.tahun ASC, t.periode ASC, t.tanggal ASC, p_scatter_bt_8.id_pengukuran ASC')
                              ->findAll();
        
        $Y_cum = 0;
        $X_cum = 0;
        
        foreach ($allScatterData as $data) {
            $Y_cum += $data['Y_US'];
            $X_cum += $data['X_TB'];
            
            $this->update($data['id_scatter'], [
                'Y_cum' => $Y_cum,
                'X_cum' => $X_cum
            ]);
        }
        
        return true;
    }
    
    /**
     * Hitung ulang kumulatif mulai dari ID pengukuran tertentu
     */
    public function recalculateCumulativeFrom($startIdPengukuran)
    {
        // Ambil semua data scatter setelah id_pengukuran yang diubah
        $allScatterData = $this->select('p_scatter_bt_8.*, t.tahun, t.periode, t.tanggal')
                              ->join('t_pengukuran_btm t', 't.id_pengukuran = p_scatter_bt_8.id_pengukuran')
                              ->where('p_scatter_bt_8.id_pengukuran >=', $startIdPengukuran)
                              ->orderBy('t.tahun ASC, t.periode ASC, t.tanggal ASC, p_scatter_bt_8.id_pengukuran ASC')
                              ->findAll();
        
        // Cari nilai kumulatif sebelumnya
        $previousData = $this->select('Y_cum, X_cum')
                           ->join('t_pengukuran_btm t', 't.id_pengukuran = p_scatter_bt_8.id_pengukuran')
                           ->where('p_scatter_bt_8.id_pengukuran <', $startIdPengukuran)
                           ->orderBy('t.tahun DESC, t.periode DESC, t.tanggal DESC, p_scatter_bt_8.id_pengukuran DESC')
                           ->first();
        
        $Y_cum = $previousData ? $previousData['Y_cum'] : 0;
        $X_cum = $previousData ? $previousData['X_cum'] : 0;
        
        // Update kumulatif untuk setiap data
        foreach ($allScatterData as $data) {
            $Y_cum += $data['Y_US'];
            $X_cum += $data['X_TB'];
            
            $this->update($data['id_scatter'], [
                'Y_cum' => $Y_cum,
                'X_cum' => $X_cum
            ]);
        }
        
        return true;
    }
    
    /**
     * Ambil data scatter by id_pengukuran
     */
    public function getByPengukuran($idPengukuran)
    {
        return $this->where('id_pengukuran', $idPengukuran)->first();
    }
    
    /**
     * Ambil semua data scatter untuk chart
     */
    public function getAllForChart()
    {
        return $this->select('p_scatter_bt_8.*, t.tahun, t.periode, t.tanggal')
                   ->join('t_pengukuran_btm t', 't.id_pengukuran = p_scatter_bt_8.id_pengukuran')
                   ->orderBy('t.tahun ASC, t.periode ASC, t.tanggal ASC')
                   ->findAll();
    }
    
    /**
     * Hapus data scatter by id_pengukuran
     */
    public function deleteByPengukuran($idPengukuran)
    {
        return $this->where('id_pengukuran', $idPengukuran)->delete();
    }

    /**
     * Ambil data scatter untuk chart (X_cum vs Y_cum)
     */
    public function getScatterChartData()
    {
        return $this->select('X_cum, Y_cum, t.tahun, t.periode, t.tanggal')
                   ->join('t_pengukuran_btm t', 't.id_pengukuran = p_scatter_bt_8.id_pengukuran')
                   ->orderBy('t.tahun ASC, t.periode ASC, t.tanggal ASC')
                   ->findAll();
    }
}