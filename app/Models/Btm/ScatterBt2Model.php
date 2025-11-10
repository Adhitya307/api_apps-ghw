<?php
namespace App\Models\Btm;

use CodeIgniter\Model;

class ScatterBt2Model extends Model
{
    protected $DBGroup          = 'btm';
    protected $table            = 'p_scatter_bt_2';
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

    public function setFirstData($idPengukuran, $Y_cum = 0, $X_cum = 0)
    {
        $data = [
            'id_pengukuran' => $idPengukuran,
            'Y_US' => 0,
            'X_TB' => 0,
            'Y_cum' => $Y_cum,
            'X_cum' => $X_cum
        ];
        
        $existing = $this->where('id_pengukuran', $idPengukuran)->first();
        
        if ($existing) {
            return $this->update($existing['id_scatter'], $data);
        } else {
            return $this->insert($data);
        }
    }

    public function calculateScatterData($idPengukuran, $aSec, $bSec, $usArah, $tbArah)
    {
        $Y_US = ($usArah == 'U') ? $aSec : (-$aSec);
        $X_TB = ($tbArah == 'T') ? $bSec : (-$bSec);
        
        $previousScatter = $this->getPreviousScatterData($idPengukuran);
        
        $previous_Y_cum = $previousScatter ? $previousScatter['Y_cum'] : 0;
        $previous_X_cum = $previousScatter ? $previousScatter['X_cum'] : 0;
        
        $Y_cum = $previous_Y_cum + $Y_US;
        $X_cum = $previous_X_cum + $X_TB;
        
        $scatterData = [
            'id_pengukuran' => $idPengukuran,
            'Y_US' => $Y_US,
            'X_TB' => $X_TB,
            'Y_cum' => $Y_cum,
            'X_cum' => $X_cum
        ];
        
        $existing = $this->where('id_pengukuran', $idPengukuran)->first();
        if ($existing) {
            return $this->update($existing['id_scatter'], $scatterData);
        } else {
            return $this->insert($scatterData);
        }
    }
    
    private function getPreviousScatterData($idPengukuran)
    {
        return $this->select('p_scatter_bt_2.*')
                   ->join('t_pengukuran_btm t', 't.id_pengukuran = p_scatter_bt_2.id_pengukuran')
                   ->where('p_scatter_bt_2.id_pengukuran <', $idPengukuran)
                   ->orderBy('t.tanggal DESC, t.tahun DESC, t.periode DESC')
                   ->first();
    }
    
    public function getByPengukuran($idPengukuran)
    {
        return $this->where('id_pengukuran', $idPengukuran)->first();
    }
}