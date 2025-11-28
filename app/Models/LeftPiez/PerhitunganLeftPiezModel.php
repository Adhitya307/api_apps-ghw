<?php
namespace App\Models\LeftPiez;

use CodeIgniter\Model;
use App\Models\LeftPiez\MetrikModel;

class PerhitunganLeftPiezModel extends Model
{
    protected $DBGroup = 'db_left_piez';
    protected $table = 'perhitungan_left_piez';
    protected $primaryKey = 'id_perhitungan';
    
    protected $allowedFields = [
        'id_pengukuran',
        'tipe_piezometer',
        'elv_piez',
        'kedalaman',
        'record_max',
        'record_min',
        'koordinat_x',
        'koordinat_y',
        't_psmetrik'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $defaultsByType = [
        'L01' => ['elv_piez' => 650.64, 'kedalaman' => 71.15],
        'L02' => ['elv_piez' => 650.66, 'kedalaman' => 73],
        'L03' => ['elv_piez' => 616.55, 'kedalaman' => 59],
        'L04' => ['elv_piez' => 580.26, 'kedalaman' => 50],
        'L05' => ['elv_piez' => 700.76, 'kedalaman' => 62],
        'L06' => ['elv_piez' => 690.09, 'kedalaman' => 62],
        'L07' => ['elv_piez' => 653.36, 'kedalaman' => 40],
        'L08' => ['elv_piez' => 659.14, 'kedalaman' => 55.5],
        'L09' => ['elv_piez' => 622.45, 'kedalaman' => 57],
        'L10' => ['elv_piez' => 580.36, 'kedalaman' => 51.5],
        'SPZ02' => ['elv_piez' => 700.08, 'kedalaman' => 70],
    ];

    protected $metrikModel;

    public function __construct()
    {
        parent::__construct();
        $this->metrikModel = new MetrikModel();
    }

    /**
     * KONVERSI SPZ02 → spz_02, L01 → l_01
     */
    private function convertToMetrikField(string $tipe)
    {
        // Pisahkan huruf dan angka: SPZ02 → ["SPZ", "02"]
        preg_match('/([A-Za-z]+)(\d+)/', $tipe, $m);

        if (!$m) return strtolower($tipe);

        $huruf = strtolower($m[1]);
        $angka = $m[2];

        return "{$huruf}_{$angka}";  // hasil akhir
    }

    /**
     * Hitung nilai
     */
    public function hitungNilai($id_pengukuran, $tipePiezometer)
    {
        $metrik = $this->metrikModel->where('id_pengukuran', $id_pengukuran)->first();
        if (!$metrik) {
            return null;
        }

        // default
        $defaults = $this->defaultsByType[$tipePiezometer] ?? $this->defaultsByType['L01'];
        $elv = $defaults['elv_piez'];
        $kedalaman = $defaults['kedalaman'];

        // existing override
        $existing = $this->where('id_pengukuran', $id_pengukuran)
                         ->where('tipe_piezometer', $tipePiezometer)
                         ->first();

        if ($existing) {
            $elv = $existing['elv_piez'] ?? $elv;
            $kedalaman = $existing['kedalaman'] ?? $kedalaman;
        }

        // gunakan format yang benar
        $fieldMetrik = $this->convertToMetrikField($tipePiezometer);

        // ambil nilai metrik
        $nilaiMetrik = $metrik[$fieldMetrik] ?? null;

        // IFERROR logic
        $result = is_numeric($nilaiMetrik)
            ? $elv - $nilaiMetrik
            : $elv - $kedalaman;

        return round($result, 4);
    }

    /**
     * INSERT
     */
    public function insert($data = null, bool $returnID = true)
    {
        if (empty($data['tipe_piezometer'])) {
            throw new \Exception('Tipe piezometer harus diisi');
        }

        $tipe = $data['tipe_piezometer'];
        $defaults = $this->defaultsByType[$tipe] ?? $this->defaultsByType['L01'];

        $data = array_merge($defaults, (array)$data);

        if (isset($data['id_pengukuran'])) {
            $data['t_psmetrik'] = $this->hitungNilai($data['id_pengukuran'], $tipe);
        }

        return parent::insert($data, $returnID);
    }

    /**
     * UPDATE
     */
    public function update($id = null, $data = null): bool
    {
        if (isset($data['id_pengukuran']) && isset($data['tipe_piezometer'])) {
            $data['t_psmetrik'] = $this->hitungNilai($data['id_pengukuran'], $data['tipe_piezometer']);
        }

        return parent::update($id, $data);
    }

    public function getByTipe($tipePiezometer)
    {
        return $this->where('tipe_piezometer', $tipePiezometer)->findAll();
    }

    public function getByPengukuranDanTipe($id_pengukuran, $tipePiezometer)
    {
        return $this->where('id_pengukuran', $id_pengukuran)
                    ->where('tipe_piezometer', $tipePiezometer)
                    ->first();
    }

    public function getDefaultKedalaman($tipePiezometer)
    {
        $defaults = $this->defaultsByType[$tipePiezometer] ?? $this->defaultsByType['L01'];
        return $defaults['kedalaman'];
    }
}
