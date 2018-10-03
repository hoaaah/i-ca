<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;

/**
 * TaValidasiPembayaranSearch represents the model behind the search form about `app\models\TaValidasiPembayaran`.
 */
class Laporan extends Model
{
    public $Kd_Laporan;
    public $Kd_Sumber;
    public $Kd_Bidang;
    public $Kd_Unit;
    public $Kd_Sub;
    public $Tgl_1;
    public $Tgl_2;
    public $Tgl_Laporan;
    public $perubahan_id;
    public $jenis_unit_id;
    public $kategori_unit_id;
    public $unit_id;
    public $tahun;
    public $kd_penerimaan_1;
    public $kd_penerimaan_2;

    public function rules()
    {
        return [
            [['Kd_Laporan', 'Kd_Urusan', 'Kd_Bidang', 'Kd_Unit', 'Kd_Sub', 'Kd_Trans_1', 'Kd_Trans_2', 'Kd_Trans_3', 'jenis_unit_id', 'pendidikan_id', 'unit_id', 'perubahan_id'], 'integer'],
            [['Tgl_1', 'Tgl_2', 'Tgl_Laporan', 'Kd_Sumber', 'Nm_Penandatangan', 'Jabatan_Penandatangan', 'NIP_Penandatangan', 'tahun'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }


    public function getKlasifikasiLaporan()
    {
        return [
            '1' => 'Form 4 Rencana Usulan Kegiatan',
            '2' => 'Form 5 Rencana Pelaksanaan Kegiatan Tahunan',               
            '6' => 'Form 6 Rencana Pelaksanaan Kegiatan Bulanan',
            '3' => 'Buku Kas Umum',
            '4' => 'Buku Pembantu Kas Tunai',
            '5' => 'Buku Pembantu Kas Bank',
            '9' => 'Buku Pembantu Pajak',   
            '7' => 'Realisasi Penggunaan Dana',
            '8' => 'Rencana Penggunaan dana per Periode',
            '10' => 'Form RKA OPD 2.2.1 (Rincian Pendapatan dan Belanja)',              
        ];
    }

    public function getRender()
    {
        $render = null;
        switch ($this->Kd_Laporan) {
            case 4:
            case 5:
            case 9:
                $render = 'laporan3';
                break;
            default:
                $render = 'laporan'.$this->Kd_Laporan;
                break;
        }

        return $render;
    }

    public function getRenderCetak()
    {
        $render = null;
        switch ($this->Kd_Laporan) {
            case 4:
            case 5:
            case 9:
                $render = 'cetaklaporan3';
                break;
            case 10:
                $render = 'cetaklaporan10';
                if($this->perubahan_id > 4) $render = 'cetaklaporan12';
                break;
            default:
                $render = 'cetaklaporan'.$this->Kd_Laporan;
                break;
        }

        return $render;
    }

    /**
     * Provide data for preview of laporan
     * If you want to get data for cetaklaporan, we will getModels with this line of code
     * first we set pagination to 0
     * $data->setPagination(['pageSize' => 0]);
     * then assign data to getModels()
     * $data = $data->getModels();
     */
    public function getDataProvider()
    {
        $dataProvider = null;
        $kd_penerimaan_1 = '%';
        $kd_penerimaan_2 = '%';
        IF($this->Kd_Sumber <> NULL){
            list($kd_penerimaan_1, $kd_penerimaan_2) = explode('.', $this->Kd_Sumber);
            IF($kd_penerimaan_1 == 0) $kd_penerimaan_1 = '%';
            IF($kd_penerimaan_2 == 0) $kd_penerimaan_2 = '%';
        }
        
        switch ($this->Kd_Laporan) {
            case 1:
                $totalCount = Yii::$app->db->createCommand("
                    SELECT COUNT(a.tahun) FROM
                    (
                        SELECT
                        a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, 
                        c.uraian_program , d.uraian_sub_program, b.uraian_kegiatan,
                        a.kd_penerimaan_1, a.kd_penerimaan_2, k.abbr,
                        e.tujuan, e.sasaran, e.target_sasaran, e.penanggung_jawab, e.kebutuhan_sumber_daya, e.mitra_kerja, e.waktu_pelaksanaan, e.indikator_kinerja, SUM(a.total) AS pagu_anggaran
                        FROM ta_rkas_history a
                        INNER JOIN ref_kegiatan b ON a.kd_program = b.kd_program AND b.kd_sub_program = a.kd_sub_program AND b.kd_kegiatan = a.kd_kegiatan
                        INNER JOIN ref_program c ON a.kd_program = c.kd_program
                        INNER JOIN ref_sub_program d ON a.kd_program = d.kd_program AND a.kd_sub_program = d.kd_sub_program
                        INNER JOIN ta_rkas_kegiatan_history e ON a.tahun = e.tahun AND a.unit_id = e.unit_id AND a.perubahan_id = e.perubahan_id AND
                            a.kd_program = e.kd_program AND a.kd_sub_program = e.kd_sub_program AND a.kd_kegiatan = e.kd_kegiatan AND a.kd_penerimaan_1 = e.kd_penerimaan_1 AND a.kd_penerimaan_2 = e.kd_penerimaan_2
                        INNER JOIN ref_penerimaan_2 k ON a.kd_penerimaan_1 = k.kd_penerimaan_1 AND a.kd_penerimaan_2 = k.kd_penerimaan_2
                        INNER JOIN ref_unit i ON a.unit_id = i.id
                        WHERE
                        a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = :perubahan_id AND IFNULL(a.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2,'') LIKE :kd_penerimaan_2 AND
                        e.tahun = :tahun AND e.unit_id = :unit_id AND e.perubahan_id = :perubahan_id AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2,'') LIKE :kd_penerimaan_2
                    
                        GROUP BY a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2
                        ORDER BY a.Kd_Rek_1, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2 ASC
                    ) a     
                    ", [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "
                        SELECT
                        a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, 
                        c.uraian_program , d.uraian_sub_program, b.uraian_kegiatan,
                        a.kd_penerimaan_1, a.kd_penerimaan_2, k.abbr,
                        e.tujuan, e.sasaran, e.target_sasaran, e.penanggung_jawab, e.kebutuhan_sumber_daya, e.mitra_kerja, e.waktu_pelaksanaan, e.indikator_kinerja, SUM(a.total) AS pagu_anggaran
                        FROM ta_rkas_history a
                        INNER JOIN ref_kegiatan b ON a.kd_program = b.kd_program AND b.kd_sub_program = a.kd_sub_program AND b.kd_kegiatan = a.kd_kegiatan
                        INNER JOIN ref_program c ON a.kd_program = c.kd_program
                        INNER JOIN ref_sub_program d ON a.kd_program = d.kd_program AND a.kd_sub_program = d.kd_sub_program
                        INNER JOIN ta_rkas_kegiatan_history e ON a.tahun = e.tahun AND a.unit_id = e.unit_id AND a.perubahan_id = e.perubahan_id AND
                            a.kd_program = e.kd_program AND a.kd_sub_program = e.kd_sub_program AND a.kd_kegiatan = e.kd_kegiatan AND a.kd_penerimaan_1 = e.kd_penerimaan_1 AND a.kd_penerimaan_2 = e.kd_penerimaan_2
                        INNER JOIN ref_penerimaan_2 k ON a.kd_penerimaan_1 = k.kd_penerimaan_1 AND a.kd_penerimaan_2 = k.kd_penerimaan_2
                        INNER JOIN ref_unit i ON a.unit_id = i.id
                        WHERE
                        a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = :perubahan_id AND IFNULL(a.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2,'') LIKE :kd_penerimaan_2 AND
                        e.tahun = :tahun AND e.unit_id = :unit_id AND e.perubahan_id = :perubahan_id AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2,'') LIKE :kd_penerimaan_2
                    
                        GROUP BY a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2
                        ORDER BY a.Kd_Rek_1, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2 ASC
                    ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);    
                break;
            case 2:
                $totalCount = Yii::$app->db->createCommand("
                    SELECT COUNT(a.tahun) FROM
                    (
                        SELECT a.*, b.bulan, b.pagu_anggaran AS pagu_anggaran_jadwal, b.rincian_pelaksanaan, b.lokasi_pelaksanaan, b.volume, b.satuan_volume
                        FROM
                        (
                            SELECT
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, 
                            c.uraian_program , d.uraian_sub_program, b.uraian_kegiatan,
                            a.kd_penerimaan_1, a.kd_penerimaan_2, k.abbr,
                            e.tujuan, e.sasaran, e.target_sasaran, e.penanggung_jawab, e.kebutuhan_sumber_daya, e.mitra_kerja, e.waktu_pelaksanaan, e.indikator_kinerja, SUM(a.total) AS pagu_anggaran
                            FROM ta_rkas_history a
                            INNER JOIN ref_kegiatan b ON a.kd_program = b.kd_program AND b.kd_sub_program = a.kd_sub_program AND b.kd_kegiatan = a.kd_kegiatan
                            INNER JOIN ref_program c ON a.kd_program = c.kd_program
                            INNER JOIN ref_sub_program d ON a.kd_program = d.kd_program AND a.kd_sub_program = d.kd_sub_program
                            INNER JOIN ta_rkas_kegiatan_history e ON a.tahun = e.tahun AND a.unit_id = e.unit_id AND a.perubahan_id = e.perubahan_id AND
                                a.kd_program = e.kd_program AND a.kd_sub_program = e.kd_sub_program AND a.kd_kegiatan = e.kd_kegiatan AND a.kd_penerimaan_1 = e.kd_penerimaan_1 AND a.kd_penerimaan_2 = e.kd_penerimaan_2
                            INNER JOIN ref_penerimaan_2 k ON a.kd_penerimaan_1 = k.kd_penerimaan_1 AND a.kd_penerimaan_2 = k.kd_penerimaan_2
                            INNER JOIN ref_unit i ON a.unit_id = i.id
                            WHERE
                            a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = :perubahan_id AND IFNULL(a.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2,'') LIKE :kd_penerimaan_2 AND
                            e.tahun = :tahun AND e.unit_id = :unit_id AND e.perubahan_id = :perubahan_id AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2,'') LIKE :kd_penerimaan_2
                        
                            GROUP BY a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ORDER BY a.Kd_Rek_1, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2 ASC
                        ) a LEFT JOIN 
                        (
                            SELECT * FROM ta_rkas_kegiatan_jadwal b  WHERE b.tahun = :tahun AND b.unit_id = :unit_id AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2,'') LIKE :kd_penerimaan_2
                        ) b  ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND
                        a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan AND a.kd_penerimaan_1 = b.kd_penerimaan_1 AND a.kd_penerimaan_2 = b.kd_penerimaan_2  
                        -- GROUP BY a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2                    
                    ) a     
                    ", [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "
                        SELECT a.*, b.bulan, b.pagu_anggaran AS pagu_anggaran_jadwal, b.rincian_pelaksanaan, b.lokasi_pelaksanaan, b.volume, b.satuan_volume
                        FROM
                        (
                            SELECT
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, 
                            c.uraian_program , d.uraian_sub_program, b.uraian_kegiatan,
                            a.kd_penerimaan_1, a.kd_penerimaan_2, k.abbr,
                            e.tujuan, e.sasaran, e.target_sasaran, e.penanggung_jawab, e.kebutuhan_sumber_daya, e.mitra_kerja, e.waktu_pelaksanaan, e.indikator_kinerja, SUM(a.total) AS pagu_anggaran
                            FROM ta_rkas_history a
                            INNER JOIN ref_kegiatan b ON a.kd_program = b.kd_program AND b.kd_sub_program = a.kd_sub_program AND b.kd_kegiatan = a.kd_kegiatan
                            INNER JOIN ref_program c ON a.kd_program = c.kd_program
                            INNER JOIN ref_sub_program d ON a.kd_program = d.kd_program AND a.kd_sub_program = d.kd_sub_program
                            INNER JOIN ta_rkas_kegiatan_history e ON a.tahun = e.tahun AND a.unit_id = e.unit_id AND a.perubahan_id = e.perubahan_id AND
                                a.kd_program = e.kd_program AND a.kd_sub_program = e.kd_sub_program AND a.kd_kegiatan = e.kd_kegiatan AND a.kd_penerimaan_1 = e.kd_penerimaan_1 AND a.kd_penerimaan_2 = e.kd_penerimaan_2
                            INNER JOIN ref_penerimaan_2 k ON a.kd_penerimaan_1 = k.kd_penerimaan_1 AND a.kd_penerimaan_2 = k.kd_penerimaan_2
                            INNER JOIN ref_unit i ON a.unit_id = i.id
                            WHERE
                            a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = :perubahan_id AND IFNULL(a.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2,'') LIKE :kd_penerimaan_2 AND
                            e.tahun = :tahun AND e.unit_id = :unit_id AND e.perubahan_id = :perubahan_id AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2,'') LIKE :kd_penerimaan_2
                        
                            GROUP BY a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ORDER BY a.Kd_Rek_1, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2 ASC
                        ) a LEFT JOIN 
                        (
                            SELECT * FROM ta_rkas_kegiatan_jadwal b  WHERE b.tahun = :tahun AND b.unit_id = :unit_id AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2,'') LIKE :kd_penerimaan_2
                        ) b  ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND
                        a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan AND a.kd_penerimaan_1 = b.kd_penerimaan_1 AND a.kd_penerimaan_2 = b.kd_penerimaan_2  
                        -- GROUP BY a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2                    
                    ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);
                break;   
            case 3:
                $totalCount = Yii::$app->db->createCommand("
                    SELECT COUNT(a.tahun) FROM
                    (
                        SELECT * FROM
                        (
                            /*SALDO AWAL */
                            SELECT
                            a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2, '' AS kode, '' AS no_bukti, '$this->tahun-01-01' AS tgl_bukti, 'Saldo Awal' AS keterangan, SUM(a.nilai) AS nilai
                            FROM
                            ta_saldo_awal a
                            INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 = b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran LIKE '%'
                            GROUP BY a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2

                            /*SALDO AWAL jika kd_penerimaan_1 adalah 1 */
                            UNION ALL
                            SELECT
                            a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2, '' AS kode, '' AS no_bukti, '$this->tahun-01-01' AS tgl_bukti, 'Saldo Awal' AS keterangan, SUM(a.nilai) AS nilai
                            FROM
                            ta_saldo_awal a
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran LIKE '%'
                            AND (:kd_penerimaan_1 = 1)
                            GROUP BY a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2

                            /*Saldo Awal sejak tanggal */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            :tgl_1 AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(a.nilai) AS nilai
                            FROM
                            (                                        
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(
                                CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                                END
                                ) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran LIKE '%'
                                GROUP BY a.tahun, a.unit_id

                                /* sisa dana */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(
                                CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                                END
                                ) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran LIKE '%'
                                AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                                GROUP BY a.tahun, a.unit_id

                                /*Potongan */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(c.nilai) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                INNER JOIN
                                ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                                INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <  :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran LIKE '%'
                                GROUP BY a.tahun, a.unit_id

                                /*Potongan Sisa Dana*/
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(c.nilai) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                INNER JOIN
                                ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                                INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <  :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran LIKE '%'
                                AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                                GROUP BY a.tahun, a.unit_id
                        
                                /*Setoran Potongan */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                '2016-01-01' AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(-(b.nilai)) AS nilai
                                FROM ta_setoran_potongan a
                                INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                                INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                                INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                                AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                                AND a.tgl_setoran < :tgl_1 AND b.pembayaran LIKE '%'
                                AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id    
                                
                                /*Setoran Potongan sisa dana */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                '2016-01-01' AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(-(b.nilai)) AS nilai
                                FROM ta_setoran_potongan a
                                INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                                INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                                INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                                AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                                AND a.tgl_setoran < :tgl_1 AND b.pembayaran LIKE '%'
                                AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                                GROUP BY a.tahun, a.unit_id

                                -- Penyesuaian Pengembalian Belanja dan pendapatan
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                b.kd_penerimaan_1,
                                b.kd_penerimaan_2,
                                CONCAT('C', a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                                a.no_bukti,
                                a.tgl_bukti,
                                a.uraian,
                                CASE a.Kd_Rek_1
                                    WHEN 4 THEN -(a.nilai)
                                    WHEN 5 THEN a.nilai
                                END
                                FROM
                                ta_koreksi AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                AND a.verifikasi = 1 AND a.koreksi_id = 2
                            ) a GROUP BY a.tahun, a.unit_id  

                            /*Transaksi */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            a.uraian,
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                            END
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            
                            /* Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1 */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            a.uraian,
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                            END
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )

                            /*Potongan Pajak Transaksi -----------------------------------------------------------------------------------------------*/
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            d.nm_potongan,
                            (c.nilai) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            INNER JOIN
                            ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                            INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran LIKE '%'
                            
                            /*Potongan Pajak Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1*/
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            d.nm_potongan,
                            (c.nilai) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            INNER JOIN
                            ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                            INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran LIKE '%'
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )

                            /* Setoran Pajak Transaksi */
                            UNION ALL
                            SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2,
                            b.kd_potongan, CONCAT(a.no_setoran, '-',b.kd_potongan) AS no_bukti, a.tgl_setoran, b.keterangan,
                            -(b.nilai) AS nilai
                            FROM ta_setoran_potongan a
                            INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                            INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan                                
                            INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                            AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                            AND a.tgl_setoran <= :tgl_2 AND a.tgl_setoran >= :tgl_1 AND b.pembayaran LIKE '%'                                
                            AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            
                            /* Setoran Pajak Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1*/
                            UNION ALL
                            SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2,
                            b.kd_potongan, CONCAT(a.no_setoran, '-',b.kd_potongan) AS no_bukti, a.tgl_setoran, b.keterangan,
                            -(b.nilai) AS nilai
                            FROM ta_setoran_potongan a
                            INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                            INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan                                
                            INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                            AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                            AND a.tgl_setoran <= :tgl_2 AND a.tgl_setoran >= :tgl_1 AND b.pembayaran LIKE '%'                                
                            AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )

                            -- Penyesuaian Pengembalian Belanja dan pendapatan
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT('C', a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            a.uraian,
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN -(a.nilai)
                                WHEN 5 THEN a.nilai
                            END
                            FROM
                            ta_koreksi AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            AND a.verifikasi = 1 AND a.koreksi_id = 2

                        ) a ORDER BY tgl_bukti, no_bukti ASC                                
                    ) a ORDER BY tgl_bukti, no_bukti ASC   
                    ", [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                        ':tgl_1' => $this->Tgl_1,
                        ':tgl_2' => $this->Tgl_2,
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "
                        SELECT * FROM
                        (
                            /*SALDO AWAL */
                            SELECT
                            a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2, '' AS kode, '' AS no_bukti, '$this->tahun-01-01' AS tgl_bukti, 'Saldo Awal' AS keterangan, SUM(a.nilai) AS nilai
                            FROM
                            ta_saldo_awal a
                            INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 = b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran LIKE '%'
                            GROUP BY a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2

                            /*SALDO AWAL jika kd_penerimaan_1 adalah 1 */
                            UNION ALL
                            SELECT
                            a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2, '' AS kode, '' AS no_bukti, '$this->tahun-01-01' AS tgl_bukti, 'Saldo Awal' AS keterangan, SUM(a.nilai) AS nilai
                            FROM
                            ta_saldo_awal a
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran LIKE '%'
                            AND (:kd_penerimaan_1 = 1)
                            GROUP BY a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2

                            /*Saldo Awal sejak tanggal */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            :tgl_1 AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(a.nilai) AS nilai
                            FROM
                            (                                        
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(
                                CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                                END
                                ) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran LIKE '%'
                                GROUP BY a.tahun, a.unit_id

                                /* sisa dana */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(
                                CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                                END
                                ) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran LIKE '%'
                                AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                                GROUP BY a.tahun, a.unit_id

                                /*Potongan */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(c.nilai) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                INNER JOIN
                                ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                                INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <  :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran LIKE '%'
                                GROUP BY a.tahun, a.unit_id

                                /*Potongan Sisa Dana*/
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(c.nilai) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                INNER JOIN
                                ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                                INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <  :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran LIKE '%'
                                AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                                GROUP BY a.tahun, a.unit_id
                        
                                /*Setoran Potongan */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                '2016-01-01' AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(-(b.nilai)) AS nilai
                                FROM ta_setoran_potongan a
                                INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                                INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                                INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                                AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                                AND a.tgl_setoran < :tgl_1 AND b.pembayaran LIKE '%'
                                AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id    
                                
                                /*Setoran Potongan sisa dana */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                '2016-01-01' AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(-(b.nilai)) AS nilai
                                FROM ta_setoran_potongan a
                                INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                                INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                                INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                                AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                                AND a.tgl_setoran < :tgl_1 AND b.pembayaran LIKE '%'
                                AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                                GROUP BY a.tahun, a.unit_id

                                -- Penyesuaian Pengembalian Belanja dan pendapatan
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                b.kd_penerimaan_1,
                                b.kd_penerimaan_2,
                                CONCAT('C', a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                                a.no_bukti,
                                a.tgl_bukti,
                                a.uraian,
                                CASE a.Kd_Rek_1
                                    WHEN 4 THEN -(a.nilai)
                                    WHEN 5 THEN a.nilai
                                END
                                FROM
                                ta_koreksi AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                AND a.verifikasi = 1 AND a.koreksi_id = 2
                            ) a GROUP BY a.tahun, a.unit_id  

                            /*Transaksi */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            a.uraian,
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                            END
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            
                            /* Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1 */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            a.uraian,
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                            END
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )

                            /*Potongan Pajak Transaksi -----------------------------------------------------------------------------------------------*/
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            d.nm_potongan,
                            (c.nilai) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            INNER JOIN
                            ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                            INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran LIKE '%'
                            
                            /*Potongan Pajak Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1*/
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            d.nm_potongan,
                            (c.nilai) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            INNER JOIN
                            ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                            INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran LIKE '%'
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )

                            /* Setoran Pajak Transaksi */
                            UNION ALL
                            SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2,
                            b.kd_potongan, CONCAT(a.no_setoran, '-',b.kd_potongan) AS no_bukti, a.tgl_setoran, b.keterangan,
                            -(b.nilai) AS nilai
                            FROM ta_setoran_potongan a
                            INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                            INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan                                
                            INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                            AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                            AND a.tgl_setoran <= :tgl_2 AND a.tgl_setoran >= :tgl_1 AND b.pembayaran LIKE '%'                                
                            AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            
                            /* Setoran Pajak Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1*/
                            UNION ALL
                            SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2,
                            b.kd_potongan, CONCAT(a.no_setoran, '-',b.kd_potongan) AS no_bukti, a.tgl_setoran, b.keterangan,
                            -(b.nilai) AS nilai
                            FROM ta_setoran_potongan a
                            INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                            INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan                                
                            INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                            AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                            AND a.tgl_setoran <= :tgl_2 AND a.tgl_setoran >= :tgl_1 AND b.pembayaran LIKE '%'                                
                            AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )

                            -- Penyesuaian Pengembalian Belanja dan pendapatan
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT('C', a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            a.uraian,
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN -(a.nilai)
                                WHEN 5 THEN a.nilai
                            END
                            FROM
                            ta_koreksi AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            AND a.verifikasi = 1 AND a.koreksi_id = 2

                        ) a ORDER BY tgl_bukti, no_bukti ASC 
                    ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                        ':tgl_1' => $this->Tgl_1,
                        ':tgl_2' => $this->Tgl_2,
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);                                  
                break;
            case 4:
                $totalCount = Yii::$app->db->createCommand("
                    SELECT COUNT(a.tahun) FROM
                    (
                        /*SALDO AWAL */
                        SELECT
                        a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2, '' AS kode, '' AS no_bukti, '$this->tahun-01-01' AS tgl_bukti, 'Saldo Awal' AS keterangan, SUM(a.nilai) AS nilai
                        FROM
                        ta_saldo_awal a
                        INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 = b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 2
                        GROUP BY a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2

                        /*SALDO AWAL jika sisa */
                        UNION ALL
                        SELECT
                        a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2, '' AS kode, '' AS no_bukti, '$this->tahun-01-01' AS tgl_bukti, 'Saldo Awal' AS keterangan, SUM(a.nilai) AS nilai
                        FROM
                        ta_saldo_awal a
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 2
                        AND (:kd_penerimaan_1 = 1 )
                        GROUP BY a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2

                        /*Saldo Awal sejak tanggal */
                        UNION ALL
                        SELECT
                        a.tahun,
                        a.unit_id,
                        '' AS kd_penerimaan_1,
                        '' AS kd_penerimaan_2,
                        '' AS kode, 
                        '' AS no_bukti,
                        :tgl_1 AS tgl_bukti,
                        'Akumulasi Transaksi' AS uraian,
                        SUM(a.nilai) AS nilai
                        FROM
                        (                                        
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            :tgl_1 AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                            END
                            ) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 2
                            GROUP BY a.tahun, a.unit_id

                            /* Transaksi Sisa */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            :tgl_1 AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                            END
                            ) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 2
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                            GROUP BY a.tahun, a.unit_id

                            /* Mutasi Kas */
                            UNION ALL
                            SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2, '' AS kode, '' AS no_bukti, '' AS tgl_bukti, '' AS uraian,
                            SUM(CASE a.kd_mutasi
                                WHEN 1 THEN a.nilai
                                WHEN 2 THEN -(a.nilai)
                            END) AS nilai
                            FROM ta_mutasi_kas a 
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1
                            AND IFNULL(a.kd_penerimaan_1, :kd_penerimaan_1) LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, :kd_penerimaan_2) LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id

                            /*Potongan */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            :tgl_1 AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(c.nilai) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            INNER JOIN
                            ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                            INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <  :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 2
                            GROUP BY a.tahun, a.unit_id

                            /*Potongan sisa */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            :tgl_1 AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(c.nilai) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            INNER JOIN
                            ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                            INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <  :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 2       
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                            GROUP BY a.tahun, a.unit_id

                            /*Setoran Potongan */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            '2016-01-01' AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(-(b.nilai)) AS nilai
                            FROM ta_setoran_potongan a
                            INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                            INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                            INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                            AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5                                        
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                            AND a.tgl_setoran < :tgl_1 AND b.pembayaran = 2
                            AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id       
                            
                            /*Setoran Potongan Sisa */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            '2016-01-01' AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(-(b.nilai)) AS nilai
                            FROM ta_setoran_potongan a
                            INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                            INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                            INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                            AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5                                        
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                            AND a.tgl_setoran < :tgl_1 AND b.pembayaran = 2
                            AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                            GROUP BY a.tahun, a.unit_id     
                            
                            -- Penyesuaian Pengembalian Belanja dan pendapatan
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT('C', a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            a.uraian,
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN -(a.nilai)
                                WHEN 5 THEN a.nilai
                            END
                            FROM
                            ta_koreksi AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            AND a.verifikasi = 1 AND a.koreksi_id = 2 AND a.pembayaran = 2                   

                        ) a GROUP BY a.tahun, a.unit_id

                        /*Transaksi */
                        UNION ALL
                        SELECT
                        a.tahun,
                        a.unit_id,
                        b.kd_penerimaan_1,
                        b.kd_penerimaan_2,
                        CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                        a.no_bukti,
                        a.tgl_bukti,
                        a.uraian,
                        CASE a.Kd_Rek_1
                            WHEN 4 THEN a.nilai
                            WHEN 5 THEN -(a.nilai)
                        END
                        FROM
                        ta_spj_rinc AS a
                        LEFT JOIN
                        (
                            SELECT 
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            FROM ta_rkas_history a 
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                            AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                        ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                        AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 2

                        /*Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1 */
                        UNION ALL
                        SELECT
                        a.tahun,
                        a.unit_id,
                        b.kd_penerimaan_1,
                        b.kd_penerimaan_2,
                        CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                        a.no_bukti,
                        a.tgl_bukti,
                        a.uraian,
                        CASE a.Kd_Rek_1
                            WHEN 4 THEN a.nilai
                            WHEN 5 THEN -(a.nilai)
                        END
                        FROM
                        ta_spj_rinc AS a
                        LEFT JOIN
                        (
                            SELECT 
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            FROM ta_rkas_history a 
                            INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                            AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                        ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                        AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 2
                        AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                        
                        /*Mutasi Kas*/
                        UNION ALL
                        SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2, '' AS kode, a.no_bukti, a.tgl_bukti, a.uraian,
                        CASE a.kd_mutasi
                            WHEN 1 THEN a.nilai
                            WHEN 2 THEN -(a.nilai)
                        END 
                        FROM ta_mutasi_kas a 
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 
                        AND IFNULL(a.kd_penerimaan_1, :kd_penerimaan_1) LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, :kd_penerimaan_2) LIKE :kd_penerimaan_2
                        
                        /*Potongan Pajak Transaksi */
                        UNION ALL
                        SELECT
                        a.tahun,
                        a.unit_id,
                        b.kd_penerimaan_1,
                        b.kd_penerimaan_2,
                        CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                        a.no_bukti,
                        a.tgl_bukti,
                        d.nm_potongan,
                        (c.nilai) AS nilai
                        FROM
                        ta_spj_rinc AS a
                        LEFT JOIN
                        (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                        ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                        AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                        INNER JOIN
                        ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                        INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 2

                        /*Potongan Pajak Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1 */
                        UNION ALL
                        SELECT
                        a.tahun,
                        a.unit_id,
                        b.kd_penerimaan_1,
                        b.kd_penerimaan_2,
                        CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                        a.no_bukti,
                        a.tgl_bukti,
                        d.nm_potongan,
                        (c.nilai) AS nilai
                        FROM
                        ta_spj_rinc AS a
                        LEFT JOIN
                        (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                        ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                        AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                        INNER JOIN
                        ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                        INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 2
                        AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                        
                        /* Setoran Pajak Transaksi */
                        UNION ALL
                        SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2,
                        b.kd_potongan, CONCAT(a.no_setoran, '-',b.kd_potongan) AS no_bukti, a.tgl_setoran, b.keterangan,
                        -(b.nilai) AS nilai
                        FROM ta_setoran_potongan a
                        INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                        INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                        INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                        LEFT JOIN
                        (
                            SELECT 
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            FROM ta_rkas_history a 
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                            AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                        ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                        AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                        AND a.tgl_setoran <= :tgl_2 AND a.tgl_setoran >= :tgl_1 AND b.pembayaran = 2
                        AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2

                        /* Setoran Pajak Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1*/
                        UNION ALL
                        SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2,
                        b.kd_potongan, CONCAT(a.no_setoran, '-',b.kd_potongan) AS no_bukti, a.tgl_setoran, b.keterangan,
                        -(b.nilai) AS nilai
                        FROM ta_setoran_potongan a
                        INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                        INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                        INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                        LEFT JOIN
                        (
                            SELECT 
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            FROM ta_rkas_history a 
                            INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                            AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                        ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                        AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                        AND a.tgl_setoran <= :tgl_2 AND a.tgl_setoran >= :tgl_1 AND b.pembayaran = 2
                        AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                        AND (:kd_penerimaan_1 NOT IN ('%', 1) )

                        -- Penyesuaian Pengembalian Belanja dan pendapatan
                        UNION ALL
                        SELECT
                        a.tahun,
                        a.unit_id,
                        b.kd_penerimaan_1,
                        b.kd_penerimaan_2,
                        CONCAT('C', a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                        a.no_bukti,
                        a.tgl_bukti,
                        a.uraian,
                        CASE a.Kd_Rek_1
                            WHEN 4 THEN -(a.nilai)
                            WHEN 5 THEN a.nilai
                        END
                        FROM
                        ta_koreksi AS a
                        LEFT JOIN
                        (
                            SELECT 
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            FROM ta_rkas_history a 
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                            AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                        ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                        AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                        AND a.verifikasi = 1 AND a.koreksi_id = 2 AND a.pembayaran = 2                             
                    ) a ORDER BY tgl_bukti, no_bukti ASC    
                    ", [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                        ':tgl_1' => $this->Tgl_1,
                        ':tgl_2' => $this->Tgl_2,
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "
                        SELECT * FROM
                        (
                            /*SALDO AWAL */
                            SELECT
                            a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2, '' AS kode, '' AS no_bukti, '$this->tahun-01-01' AS tgl_bukti, 'Saldo Awal' AS keterangan, SUM(a.nilai) AS nilai
                            FROM
                            ta_saldo_awal a
                            INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 = b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 2
                            GROUP BY a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2

                            /*SALDO AWAL jika sisa */
                            UNION ALL
                            SELECT
                            a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2, '' AS kode, '' AS no_bukti, '$this->tahun-01-01' AS tgl_bukti, 'Saldo Awal' AS keterangan, SUM(a.nilai) AS nilai
                            FROM
                            ta_saldo_awal a
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 2
                            AND (:kd_penerimaan_1 = 1 )
                            GROUP BY a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2

                            /*Saldo Awal sejak tanggal */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            :tgl_1 AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(a.nilai) AS nilai
                            FROM
                            (                                        
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(
                                CASE a.Kd_Rek_1
                                    WHEN 4 THEN a.nilai
                                    WHEN 5 THEN -(a.nilai)
                                END
                                ) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 2
                                GROUP BY a.tahun, a.unit_id

                                /* Transaksi Sisa */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(
                                CASE a.Kd_Rek_1
                                    WHEN 4 THEN a.nilai
                                    WHEN 5 THEN -(a.nilai)
                                END
                                ) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 2
                                AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                                GROUP BY a.tahun, a.unit_id

                                /* Mutasi Kas */
                                UNION ALL
                                SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2, '' AS kode, '' AS no_bukti, '' AS tgl_bukti, '' AS uraian,
                                SUM(CASE a.kd_mutasi
                                    WHEN 1 THEN a.nilai
                                    WHEN 2 THEN -(a.nilai)
                                END) AS nilai
                                FROM ta_mutasi_kas a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1
                                AND IFNULL(a.kd_penerimaan_1, :kd_penerimaan_1) LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, :kd_penerimaan_2) LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id

                                /*Potongan */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(c.nilai) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                        SELECT 
                                        a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                        FROM ta_rkas_history a 
                                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                        AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                        GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                INNER JOIN
                                ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                                INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <  :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 2
                                GROUP BY a.tahun, a.unit_id

                                /*Potongan sisa */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(c.nilai) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                INNER JOIN
                                ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                                INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <  :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 2       
                                AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                                GROUP BY a.tahun, a.unit_id

                                /*Setoran Potongan */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                '2016-01-01' AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(-(b.nilai)) AS nilai
                                FROM ta_setoran_potongan a
                                INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                                INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                                INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                                AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5                                        
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                                AND a.tgl_setoran < :tgl_1 AND b.pembayaran = 2
                                AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id       
                                
                                /*Setoran Potongan Sisa */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                '2016-01-01' AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(-(b.nilai)) AS nilai
                                FROM ta_setoran_potongan a
                                INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                                INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                                INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                                AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5                                        
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                                AND a.tgl_setoran < :tgl_1 AND b.pembayaran = 2
                                AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                                GROUP BY a.tahun, a.unit_id     
                                
                                -- Penyesuaian Pengembalian Belanja dan pendapatan
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                b.kd_penerimaan_1,
                                b.kd_penerimaan_2,
                                CONCAT('C', a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                                a.no_bukti,
                                a.tgl_bukti,
                                a.uraian,
                                CASE a.Kd_Rek_1
                                    WHEN 4 THEN -(a.nilai)
                                    WHEN 5 THEN a.nilai
                                END
                                FROM
                                ta_koreksi AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                AND a.verifikasi = 1 AND a.koreksi_id = 2 AND a.pembayaran = 2                   

                            ) a GROUP BY a.tahun, a.unit_id

                            /*Transaksi */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            a.uraian,
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                            END
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 2

                            /*Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1 */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            a.uraian,
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                            END
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 2
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                            
                            /*Mutasi Kas*/
                            UNION ALL
                            SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2, '' AS kode, a.no_bukti, a.tgl_bukti, a.uraian,
                            CASE a.kd_mutasi
                                WHEN 1 THEN a.nilai
                                WHEN 2 THEN -(a.nilai)
                            END 
                            FROM ta_mutasi_kas a 
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 
                            AND IFNULL(a.kd_penerimaan_1, :kd_penerimaan_1) LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, :kd_penerimaan_2) LIKE :kd_penerimaan_2
                            
                            /*Potongan Pajak Transaksi */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            d.nm_potongan,
                            (c.nilai) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            INNER JOIN
                            ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                            INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 2

                            /*Potongan Pajak Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1 */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            d.nm_potongan,
                            (c.nilai) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            INNER JOIN
                            ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                            INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 2
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                            
                            /* Setoran Pajak Transaksi */
                            UNION ALL
                            SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2,
                            b.kd_potongan, CONCAT(a.no_setoran, '-',b.kd_potongan) AS no_bukti, a.tgl_setoran, b.keterangan,
                            -(b.nilai) AS nilai
                            FROM ta_setoran_potongan a
                            INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                            INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                            INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                            AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                            AND a.tgl_setoran <= :tgl_2 AND a.tgl_setoran >= :tgl_1 AND b.pembayaran = 2
                            AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2

                            /* Setoran Pajak Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1*/
                            UNION ALL
                            SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2,
                            b.kd_potongan, CONCAT(a.no_setoran, '-',b.kd_potongan) AS no_bukti, a.tgl_setoran, b.keterangan,
                            -(b.nilai) AS nilai
                            FROM ta_setoran_potongan a
                            INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                            INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                            INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                            AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                            AND a.tgl_setoran <= :tgl_2 AND a.tgl_setoran >= :tgl_1 AND b.pembayaran = 2
                            AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )

                            -- Penyesuaian Pengembalian Belanja dan pendapatan
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT('C', a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            a.uraian,
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN -(a.nilai)
                                WHEN 5 THEN a.nilai
                            END
                            FROM
                            ta_koreksi AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            AND a.verifikasi = 1 AND a.koreksi_id = 2 AND a.pembayaran = 2
                        ) a ORDER BY tgl_bukti, no_bukti ASC     
                    ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                        ':tgl_1' => $this->Tgl_1,
                        ':tgl_2' => $this->Tgl_2,
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);                                  
                break;  
            case 5:
                $totalCount = Yii::$app->db->createCommand("
                    SELECT COUNT(a.tahun) FROM
                    (
                        /*SALDO AWAL */
                        SELECT
                        a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2, '' AS kode, '' AS no_bukti, '$this->tahun-01-01' AS tgl_bukti, 'Saldo Awal' AS keterangan, SUM(a.nilai) AS nilai
                        FROM
                        ta_saldo_awal a
                        INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 = b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 1
                        GROUP BY a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2

                        /*SALDO AWAL sisa */
                        UNION ALL
                        SELECT
                        a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2, '' AS kode, '' AS no_bukti, '$this->tahun-01-01' AS tgl_bukti, 'Saldo Awal' AS keterangan, SUM(a.nilai) AS nilai
                        FROM
                        ta_saldo_awal a
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 1
                        AND (:kd_penerimaan_1 = 1)
                        GROUP BY a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2

                        /*Saldo Awal sejak tanggal */
                        UNION ALL
                        SELECT
                        a.tahun,
                        a.unit_id,
                        '' AS kd_penerimaan_1,
                        '' AS kd_penerimaan_2,
                        '' AS kode, 
                        '' AS no_bukti,
                        :tgl_1 AS tgl_bukti,
                        'Akumulasi Transaksi' AS uraian,
                        SUM(a.nilai) AS nilai
                        FROM
                        (                                        
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            :tgl_1 AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                            END
                            ) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 1
                            GROUP BY a.tahun, a.unit_id

                            /* Transaksi Sisa */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            :tgl_1 AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                            END
                            ) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 1
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                            GROUP BY a.tahun, a.unit_id

                            UNION ALL
                            SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2, '' AS kode, '' AS no_bukti, '' AS tgl_bukti, '' AS uraian,
                            SUM(CASE a.kd_mutasi
                                WHEN 2 THEN a.nilai
                                WHEN 1 THEN -(a.nilai)
                            END) AS nilai
                            FROM ta_mutasi_kas a 
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1
                            AND IFNULL(a.kd_penerimaan_1, :kd_penerimaan_1) LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, :kd_penerimaan_2) LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id

                            /*Potongan */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            :tgl_1 AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(c.nilai) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            INNER JOIN
                            ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                            INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <  :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 1
                            GROUP BY a.tahun, a.unit_id

                            /*Potongan sisa */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            :tgl_1 AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(c.nilai) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            INNER JOIN
                            ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                            INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <  :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 1
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                            GROUP BY a.tahun, a.unit_id

                            /*Setoran Potongan */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            '2016-01-01' AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(-(b.nilai)) AS nilai
                            FROM ta_setoran_potongan a
                            INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                            INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                            INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                            AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                            AND a.tgl_setoran < :tgl_1 AND b.pembayaran = 1
                            AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id
                            
                            /*Setoran Potongan sisa */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            '2016-01-01' AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(-(b.nilai)) AS nilai
                            FROM ta_setoran_potongan a
                            INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                            INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                            INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                            AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                            AND a.tgl_setoran < :tgl_1 AND b.pembayaran = 1
                            AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                            GROUP BY a.tahun, a.unit_id
                            
                            -- Penyesuaian Pengembalian Belanja dan pendapatan
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT('C', a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            a.uraian,
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN -(a.nilai)
                                WHEN 5 THEN a.nilai
                            END
                            FROM
                            ta_koreksi AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            AND a.verifikasi = 1 AND a.koreksi_id = 2 AND a.pembayaran = 1                                    
                            
                        ) a GROUP BY a.tahun, a.unit_id

                        /*Transaksi */
                        UNION ALL
                        SELECT
                        a.tahun,
                        a.unit_id,
                        b.kd_penerimaan_1,
                        b.kd_penerimaan_2,
                        CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                        a.no_bukti,
                        a.tgl_bukti,
                        a.uraian,
                        CASE a.Kd_Rek_1
                            WHEN 4 THEN a.nilai
                            WHEN 5 THEN -(a.nilai)
                        END
                        FROM
                        ta_spj_rinc AS a
                        LEFT JOIN
                        (
                            SELECT 
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            FROM ta_rkas_history a 
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                            AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                        ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                        AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 1

                        /*Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1 */
                        UNION ALL
                        SELECT
                        a.tahun,
                        a.unit_id,
                        b.kd_penerimaan_1,
                        b.kd_penerimaan_2,
                        CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                        a.no_bukti,
                        a.tgl_bukti,
                        a.uraian,
                        CASE a.Kd_Rek_1
                            WHEN 4 THEN a.nilai
                            WHEN 5 THEN -(a.nilai)
                        END
                        FROM
                        ta_spj_rinc AS a
                        LEFT JOIN
                        (
                            SELECT 
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            FROM ta_rkas_history a 
                            INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                            AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                        ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                        AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 1
                        AND (:kd_penerimaan_1 NOT IN ('%', 1) )

                        /*Mutasi Kas*/
                        UNION ALL
                        SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2, '' AS kode, a.no_bukti, a.tgl_bukti, a.uraian,
                        CASE a.kd_mutasi
                            WHEN 2 THEN a.nilai
                            WHEN 1 THEN -(a.nilai)
                        END 
                        FROM ta_mutasi_kas a 
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 
                        AND IFNULL(a.kd_penerimaan_1, :kd_penerimaan_1) LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, :kd_penerimaan_2) LIKE :kd_penerimaan_2

                        /*Potongan Pajak Transaksi */
                        UNION ALL
                        SELECT
                        a.tahun,
                        a.unit_id,
                        b.kd_penerimaan_1,
                        b.kd_penerimaan_2,
                        CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                        a.no_bukti,
                        a.tgl_bukti,
                        d.nm_potongan,
                        (c.nilai) AS nilai
                        FROM
                        ta_spj_rinc AS a
                        LEFT JOIN
                        (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                        ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                        AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                        INNER JOIN
                        ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                        INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 1

                        /*Potongan Pajak Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1 */
                        UNION ALL
                        SELECT
                        a.tahun,
                        a.unit_id,
                        b.kd_penerimaan_1,
                        b.kd_penerimaan_2,
                        CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                        a.no_bukti,
                        a.tgl_bukti,
                        d.nm_potongan,
                        (c.nilai) AS nilai
                        FROM
                        ta_spj_rinc AS a
                        LEFT JOIN
                        (
                            SELECT 
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            FROM ta_rkas_history a 
                            INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                            AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                        ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                        AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                        INNER JOIN
                        ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                        INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 1
                        AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                        
                        /* Setoran Pajak Transaksi */
                        UNION ALL
                        SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2,
                        b.kd_potongan, CONCAT(a.no_setoran, '-',b.kd_potongan) AS no_bukti, a.tgl_setoran, b.keterangan,
                        -(b.nilai) AS nilai
                        FROM ta_setoran_potongan a
                        INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                        INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                        INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                        LEFT JOIN
                        (
                            SELECT 
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            FROM ta_rkas_history a 
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                            AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                        ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                        AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5            
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                        AND a.tgl_setoran <= :tgl_2 AND a.tgl_setoran >= :tgl_1 AND b.pembayaran = 1
                        AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2    
                        
                        /* Setoran Pajak Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1 */
                        UNION ALL
                        SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2,
                        b.kd_potongan, CONCAT(a.no_setoran, '-',b.kd_potongan) AS no_bukti, a.tgl_setoran, b.keterangan,
                        -(b.nilai) AS nilai
                        FROM ta_setoran_potongan a
                        INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                        INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                        INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                        LEFT JOIN
                        (
                            SELECT 
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            FROM ta_rkas_history a 
                            INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                            AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                        ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                        AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5            
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                        AND a.tgl_setoran <= :tgl_2 AND a.tgl_setoran >= :tgl_1 AND b.pembayaran = 1
                        AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2    
                        AND (:kd_penerimaan_1 NOT IN ('%', 1) )

                        -- Penyesuaian Pengembalian Belanja dan pendapatan
                        UNION ALL
                        SELECT
                        a.tahun,
                        a.unit_id,
                        b.kd_penerimaan_1,
                        b.kd_penerimaan_2,
                        CONCAT('C', a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                        a.no_bukti,
                        a.tgl_bukti,
                        a.uraian,
                        CASE a.Kd_Rek_1
                            WHEN 4 THEN -(a.nilai)
                            WHEN 5 THEN a.nilai
                        END
                        FROM
                        ta_koreksi AS a
                        LEFT JOIN
                        (
                            SELECT 
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            FROM ta_rkas_history a 
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                            AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                        ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                        AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                        AND a.verifikasi = 1 AND a.koreksi_id = 2 AND a.pembayaran = 1                                     
                    ) a ORDER BY tgl_bukti, no_bukti ASC    
                    ", [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                        ':tgl_1' => $this->Tgl_1,
                        ':tgl_2' => $this->Tgl_2,
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "
                        SELECT * FROM
                        (
                            /*SALDO AWAL */
                            SELECT
                            a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2, '' AS kode, '' AS no_bukti, '$this->tahun-01-01' AS tgl_bukti, 'Saldo Awal' AS keterangan, SUM(a.nilai) AS nilai
                            FROM
                            ta_saldo_awal a
                            INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 = b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 1
                            GROUP BY a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2

                            /*SALDO AWAL sisa */
                            UNION ALL
                            SELECT
                            a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2, '' AS kode, '' AS no_bukti, '$this->tahun-01-01' AS tgl_bukti, 'Saldo Awal' AS keterangan, SUM(a.nilai) AS nilai
                            FROM
                            ta_saldo_awal a
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 1
                            AND (:kd_penerimaan_1 = 1)
                            GROUP BY a.tahun, a.unit_id, a.kd_penerimaan_1, a.kd_penerimaan_2

                            /*Saldo Awal sejak tanggal */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            '' AS kd_penerimaan_1,
                            '' AS kd_penerimaan_2,
                            '' AS kode, 
                            '' AS no_bukti,
                            :tgl_1 AS tgl_bukti,
                            'Akumulasi Transaksi' AS uraian,
                            SUM(a.nilai) AS nilai
                            FROM
                            (                                        
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(
                                CASE a.Kd_Rek_1
                                    WHEN 4 THEN a.nilai
                                    WHEN 5 THEN -(a.nilai)
                                END
                                ) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 1
                                GROUP BY a.tahun, a.unit_id

                                /* Transaksi Sisa */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(
                                CASE a.Kd_Rek_1
                                    WHEN 4 THEN a.nilai
                                    WHEN 5 THEN -(a.nilai)
                                END
                                ) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 1
                                AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                                GROUP BY a.tahun, a.unit_id

                                UNION ALL
                                SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2, '' AS kode, '' AS no_bukti, '' AS tgl_bukti, '' AS uraian,
                                SUM(CASE a.kd_mutasi
                                    WHEN 2 THEN a.nilai
                                    WHEN 1 THEN -(a.nilai)
                                END) AS nilai
                                FROM ta_mutasi_kas a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti < :tgl_1
                                AND IFNULL(a.kd_penerimaan_1, :kd_penerimaan_1) LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, :kd_penerimaan_2) LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id

                                /*Potongan */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(c.nilai) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                INNER JOIN
                                ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                                INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <  :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 1
                                GROUP BY a.tahun, a.unit_id

                                /*Potongan sisa */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                :tgl_1 AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(c.nilai) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                INNER JOIN
                                ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                                INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <  :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 1
                                AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                                GROUP BY a.tahun, a.unit_id

                                /*Setoran Potongan */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                '2016-01-01' AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(-(b.nilai)) AS nilai
                                FROM ta_setoran_potongan a
                                INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                                INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                                INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                                AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                                AND a.tgl_setoran < :tgl_1 AND b.pembayaran = 1
                                AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id
                                
                                /*Setoran Potongan sisa */
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                '' AS kd_penerimaan_1,
                                '' AS kd_penerimaan_2,
                                '' AS kode, 
                                '' AS no_bukti,
                                '2016-01-01' AS tgl_bukti,
                                'Akumulasi Transaksi' AS uraian,
                                SUM(-(b.nilai)) AS nilai
                                FROM ta_setoran_potongan a
                                INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                                INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                                INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                                AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                                AND a.tgl_setoran < :tgl_1 AND b.pembayaran = 1
                                AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                                GROUP BY a.tahun, a.unit_id
                                
                                -- Penyesuaian Pengembalian Belanja dan pendapatan
                                UNION ALL
                                SELECT
                                a.tahun,
                                a.unit_id,
                                b.kd_penerimaan_1,
                                b.kd_penerimaan_2,
                                CONCAT('C', a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                                a.no_bukti,
                                a.tgl_bukti,
                                a.uraian,
                                CASE a.Kd_Rek_1
                                    WHEN 4 THEN -(a.nilai)
                                    WHEN 5 THEN a.nilai
                                END
                                FROM
                                ta_koreksi AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                AND a.verifikasi = 1 AND a.koreksi_id = 2 AND a.pembayaran = 1                                    
                                
                            ) a GROUP BY a.tahun, a.unit_id

                            /*Transaksi */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            a.uraian,
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                            END
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 1

                            /*Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1 */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            a.uraian,
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN a.nilai
                                WHEN 5 THEN -(a.nilai)
                            END
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2  AND a.pembayaran = 1
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )

                            /*Mutasi Kas*/
                            UNION ALL
                            SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2, '' AS kode, a.no_bukti, a.tgl_bukti, a.uraian,
                            CASE a.kd_mutasi
                                WHEN 2 THEN a.nilai
                                WHEN 1 THEN -(a.nilai)
                            END 
                            FROM ta_mutasi_kas a 
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 
                            AND IFNULL(a.kd_penerimaan_1, :kd_penerimaan_1) LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, :kd_penerimaan_2) LIKE :kd_penerimaan_2

                            /*Potongan Pajak Transaksi */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            d.nm_potongan,
                            (c.nilai) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            INNER JOIN
                            ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                            INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 1

                            /*Potongan Pajak Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1 */
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT(a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            d.nm_potongan,
                            (c.nilai) AS nilai
                            FROM
                            ta_spj_rinc AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            INNER JOIN
                            ta_spj_pot c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.no_bukti = c.no_bukti
                            INNER JOIN ref_potongan d ON c.kd_potongan =  d.kd_potongan
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND a.pembayaran = 1
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )
                            
                            /* Setoran Pajak Transaksi */
                            UNION ALL
                            SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2,
                            b.kd_potongan, CONCAT(a.no_setoran, '-',b.kd_potongan) AS no_bukti, a.tgl_setoran, b.keterangan,
                            -(b.nilai) AS nilai
                            FROM ta_setoran_potongan a
                            INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                            INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                            INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                            AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5            
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                            AND a.tgl_setoran <= :tgl_2 AND a.tgl_setoran >= :tgl_1 AND b.pembayaran = 1
                            AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2    
                            
                            /* Setoran Pajak Transaksi untuk sisa dana, menampilkan rekening penerimaan 1 saat seleksi kd_penerimaan_1 != '%' atau 1 */
                            UNION ALL
                            SELECT a.tahun, a.unit_id, '' AS kd_penerimaan_1, '' AS kd_penerimaan_2,
                            b.kd_potongan, CONCAT(a.no_setoran, '-',b.kd_potongan) AS no_bukti, a.tgl_setoran, b.keterangan,
                            -(b.nilai) AS nilai
                            FROM ta_setoran_potongan a
                            INNER JOIN ta_setoran_potongan_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                            INNER JOIN ref_potongan c ON b.kd_potongan = c.kd_potongan
                            INNER JOIN ta_spj_rinc d ON b.tahun = d.tahun AND b.unit_id = d.unit_id AND b.no_bukti = d.no_bukti
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 =  b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, b.kd_penerimaan_1, b.kd_penerimaan_2
                            ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                            AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5            
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                            AND a.tgl_setoran <= :tgl_2 AND a.tgl_setoran >= :tgl_1 AND b.pembayaran = 1
                            AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2    
                            AND (:kd_penerimaan_1 NOT IN ('%', 1) )

                            -- Penyesuaian Pengembalian Belanja dan pendapatan
                            UNION ALL
                            SELECT
                            a.tahun,
                            a.unit_id,
                            b.kd_penerimaan_1,
                            b.kd_penerimaan_2,
                            CONCAT('C', a.kd_program, RIGHT(CONCAT('0',a.kd_sub_program),2), RIGHT(CONCAT('0',a.kd_kegiatan),2), '.', a.Kd_Rek_3, RIGHT(CONCAT('0',a.Kd_Rek_4),2), RIGHT(CONCAT('0',a.Kd_Rek_5),2)) AS kode,
                            a.no_bukti,
                            a.tgl_bukti,
                            a.uraian,
                            CASE a.Kd_Rek_1
                                WHEN 4 THEN -(a.nilai)
                                WHEN 5 THEN a.nilai
                            END
                            FROM
                            ta_koreksi AS a
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                            AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            AND a.verifikasi = 1 AND a.koreksi_id = 2 AND a.pembayaran = 1
                        ) a ORDER BY tgl_bukti, no_bukti ASC
                    ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                        ':tgl_1' => $this->Tgl_1,
                        ':tgl_2' => $this->Tgl_2,
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);                                  
                break;                                                                                         
            case 6:
                $totalCount = Yii::$app->db->createCommand("
                    SELECT COUNT(a.kd_program) FROM
                    (   
                        SELECT a.*, b.bulan, b.pagu_anggaran AS pagu_anggaran_jadwal, b.rincian_pelaksanaan, b.lokasi_pelaksanaan, b.volume, b.satuan_volume
                        FROM
                        (
                            SELECT
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, 
                            c.uraian_program , d.uraian_sub_program, b.uraian_kegiatan,
                            a.kd_penerimaan_1, a.kd_penerimaan_2, k.abbr,
                            e.tujuan, e.sasaran, e.target_sasaran, e.penanggung_jawab, e.kebutuhan_sumber_daya, e.mitra_kerja, e.waktu_pelaksanaan, e.indikator_kinerja, SUM(a.total) AS pagu_anggaran
                            FROM ta_rkas_history a
                            INNER JOIN ref_kegiatan b ON a.kd_program = b.kd_program AND b.kd_sub_program = a.kd_sub_program AND b.kd_kegiatan = a.kd_kegiatan
                            INNER JOIN ref_program c ON a.kd_program = c.kd_program
                            INNER JOIN ref_sub_program d ON a.kd_program = d.kd_program AND a.kd_sub_program = d.kd_sub_program
                            INNER JOIN ta_rkas_kegiatan_history e ON a.tahun = e.tahun AND a.unit_id = e.unit_id AND a.perubahan_id = e.perubahan_id AND
                                a.kd_program = e.kd_program AND a.kd_sub_program = e.kd_sub_program AND a.kd_kegiatan = e.kd_kegiatan AND a.kd_penerimaan_1 = e.kd_penerimaan_1 AND a.kd_penerimaan_2 = e.kd_penerimaan_2
                            INNER JOIN ref_penerimaan_2 k ON a.kd_penerimaan_1 = k.kd_penerimaan_1 AND a.kd_penerimaan_2 = k.kd_penerimaan_2
                            INNER JOIN ref_unit i ON a.unit_id = i.id
                            WHERE
                            a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = :perubahan_id AND IFNULL(a.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2,'') LIKE :kd_penerimaan_2 AND
                            e.tahun = :tahun AND e.unit_id = :unit_id AND e.perubahan_id = :perubahan_id AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2,'') LIKE :kd_penerimaan_2
                            AND a.Kd_Rek_1 = 5
                            GROUP BY a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ORDER BY a.Kd_Rek_1, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2 ASC
                        ) a LEFT JOIN 
                        (
                            SELECT * FROM ta_rkas_kegiatan_jadwal b  WHERE b.tahun = :tahun AND b.unit_id = :unit_id AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2,'') LIKE :kd_penerimaan_2
                        ) b  ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND
                        a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan AND a.kd_penerimaan_1 = b.kd_penerimaan_1 AND a.kd_penerimaan_2 = b.kd_penerimaan_2  
                        GROUP BY bulan, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2
                    ) a
                    ", [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "
                        SELECT a.*, b.bulan, b.pagu_anggaran AS pagu_anggaran_jadwal, b.rincian_pelaksanaan, b.lokasi_pelaksanaan, b.volume, b.satuan_volume
                        FROM
                        (
                            SELECT
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, 
                            c.uraian_program , d.uraian_sub_program, b.uraian_kegiatan,
                            a.kd_penerimaan_1, a.kd_penerimaan_2, k.abbr,
                            e.tujuan, e.sasaran, e.target_sasaran, e.penanggung_jawab, e.kebutuhan_sumber_daya, e.mitra_kerja, e.waktu_pelaksanaan, e.indikator_kinerja, SUM(a.total) AS pagu_anggaran
                            FROM ta_rkas_history a
                            INNER JOIN ref_kegiatan b ON a.kd_program = b.kd_program AND b.kd_sub_program = a.kd_sub_program AND b.kd_kegiatan = a.kd_kegiatan
                            INNER JOIN ref_program c ON a.kd_program = c.kd_program
                            INNER JOIN ref_sub_program d ON a.kd_program = d.kd_program AND a.kd_sub_program = d.kd_sub_program
                            INNER JOIN ta_rkas_kegiatan_history e ON a.tahun = e.tahun AND a.unit_id = e.unit_id AND a.perubahan_id = e.perubahan_id AND
                                a.kd_program = e.kd_program AND a.kd_sub_program = e.kd_sub_program AND a.kd_kegiatan = e.kd_kegiatan AND a.kd_penerimaan_1 = e.kd_penerimaan_1 AND a.kd_penerimaan_2 = e.kd_penerimaan_2
                            INNER JOIN ref_penerimaan_2 k ON a.kd_penerimaan_1 = k.kd_penerimaan_1 AND a.kd_penerimaan_2 = k.kd_penerimaan_2
                            INNER JOIN ref_unit i ON a.unit_id = i.id
                            WHERE
                            a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = :perubahan_id AND IFNULL(a.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2,'') LIKE :kd_penerimaan_2 AND
                            e.tahun = :tahun AND e.unit_id = :unit_id AND e.perubahan_id = :perubahan_id AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2,'') LIKE :kd_penerimaan_2
                            AND a.Kd_Rek_1 = 5
                            GROUP BY a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ORDER BY a.Kd_Rek_1, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2 ASC
                        ) a LEFT JOIN 
                        (
                            SELECT * FROM ta_rkas_kegiatan_jadwal b  WHERE b.tahun = :tahun AND b.unit_id = :unit_id AND IFNULL(b.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2,'') LIKE :kd_penerimaan_2
                        ) b  ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND
                        a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan AND a.kd_penerimaan_1 = b.kd_penerimaan_1 AND a.kd_penerimaan_2 = b.kd_penerimaan_2  
                        GROUP BY bulan, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2                              
                    ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);   

                break;   
            case 7:
                $totalCount = Yii::$app->db->createCommand("
                    SELECT COUNT(a.tahun) FROM(
                        SELECT
                        a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, SUM(a.total) AS anggaran
                        FROM
                        ta_rkas_history a
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = :perubahan_id
                        AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                        GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1
                    ) a
                    ", [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "
                        SELECT a.tahun, a.unit_id, a.kd_program, c.uraian_program, a.kd_sub_program, d.uraian_sub_program, a.kd_kegiatan, e.uraian_kegiatan, a.Kd_Rek_1, a.anggaran,
                        IFNULL(b.nilai,0) AS realisasi
                        FROM
                        (
                            SELECT
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, SUM(a.total) AS anggaran
                            FROM
                            ta_rkas_history a
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = :perubahan_id
                            AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1
                        ) a
                        LEFT JOIN
                        (
                            SELECT
                            a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, SUM(a.nilai) AS nilai
                            FROM
                            (
                                SELECT
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                -- penyesuaian pengembalian
                                UNION ALL
                                SELECT
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, -(a.nilai) AS nilai
                                FROM
                                ta_koreksi AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                AND a.koreksi_id = 2 AND a.verifikasi = 1
                                -- penyesuaian koreksi tambah
                                UNION ALL
                                SELECT
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, (a.nilai) AS nilai
                                FROM
                                ta_koreksi AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                AND a.koreksi_id = 1 AND a.verifikasi = 1
                                -- penyesuaian koreksi kurang
                                UNION ALL
                                SELECT
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, (-a.nilai) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                INNER JOIN ta_koreksi c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.kd_program = c.kd_program AND a.kd_sub_program = c.kd_sub_program AND a.kd_kegiatan = c.kd_kegiatan 
                                AND a.Kd_Rek_1 = c.Kd_Rek_1 AND a.Kd_Rek_2 = c.Kd_Rek_2 AND a.Kd_Rek_3 = c.Kd_Rek_3 AND c.Kd_Rek_4 = c.Kd_Rek_4 AND a.Kd_Rek_5 = c.Kd_Rek_5
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND IFNULL(b.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(b.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                AND c.koreksi_id = 1 AND c.verifikasi = 1  AND c.tgl_bukti <= :tgl_2 AND c.tgl_bukti >= :tgl_1  
                            ) a
                            GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1
                        ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan AND a.Kd_Rek_1 = b.Kd_Rek_1
                        LEFT JOIN ref_program c ON a.kd_program = c.kd_program
                        LEFT JOIN ref_sub_program d ON a.kd_program = d.kd_program AND a.kd_sub_program = d.kd_sub_program
                        LEFT JOIN ref_kegiatan e ON a.kd_program = e.kd_program AND a.kd_sub_program = e.kd_sub_program AND a.kd_kegiatan = e.kd_kegiatan
                        ORDER BY a.tahun, a.unit_id, a.kd_program, a.Kd_Rek_1 ASC 

                    ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                        ':tgl_1' => $this->Tgl_1,
                        ':tgl_2' => $this->Tgl_2,
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);   

                break;
            case 8:
                $totalCount = Yii::$app->db->createCommand("
                    SELECT COUNT(a.tahun) FROM(
                        SELECT
                        a.tahun, a.unit_id, IFNULL(a.komponen_id, 0) AS komponen_id, IFNULL(b.komponen, 'Non-Komponen') AS komponen, a.Kd_Rek_1, SUM(a.total) AS anggaran
                        FROM
                        ta_rkas_history a
                        LEFT JOIN ref_komponen_bos b ON a.komponen_id = b.id
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = :perubahan_id AND a.Kd_Rek_1 = 5
                        AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                        GROUP BY a.tahun, a.unit_id, a.komponen_id, a.Kd_Rek_1
                    ) a
                    ", [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "                                
                        SELECT
                        a.tahun, a.unit_id, IFNULL(a.komponen_id, 0) AS komponen_id, IFNULL(b.komponen, 'Non-Komponen') AS komponen, a.Kd_Rek_1, SUM(a.total) AS anggaran
                        FROM
                        ta_rkas_history a
                        LEFT JOIN ref_komponen_bos b ON a.komponen_id = b.id
                        WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = :perubahan_id AND a.Kd_Rek_1 = 5
                        AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                        GROUP BY a.tahun, a.unit_id, a.komponen_id, a.Kd_Rek_1
                    ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);   

                break;
            case 9:
                $totalCount = Yii::$app->db->createCommand("
                        SELECT COUNT(a.tahun) FROM
                        (
                            #Saldo Awal
                            SELECT a.tahun, a.unit_id, a.kd_potongan AS kode, '' AS no_bukti, '$this->tahun-01-01' AS tgl_bukti, CONCAT('Saldo Awal ',b.nm_potongan) AS keterangan, a.nilai
                            FROM ta_saldo_awal_potongan a
                            INNER JOIN ref_potongan b ON a.kd_potongan = b.kd_potongan
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                            AND IFNULL(a.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            #akumulasi transaksi
                            UNION ALL
                            SELECT a.tahun, a.unit_id, a.kd_potongan, '' AS no_bukti, :tgl_1, CONCAT('Saldo Akumulasi ', b.nm_potongan) AS uraian, SUM(nilai) AS nilai
                            FROM (
                                SELECT a.tahun, a.unit_id, a.kd_potongan, b.no_bukti, b.tgl_bukti, b.uraian, a.nilai
                                FROM ta_spj_pot a
                                INNER JOIN ta_spj_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_bukti = b.no_bukti
                                                                    
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) c ON b.tahun = c.tahun AND b.unit_id = c.unit_id AND b.kd_program = c.kd_program AND b.kd_sub_program = c.kd_sub_program AND b.kd_kegiatan = c.kd_kegiatan 
                                AND b.Kd_Rek_1 = c.Kd_Rek_1 AND b.Kd_Rek_2 = c.Kd_Rek_2 AND b.Kd_Rek_3 = c.Kd_Rek_3 AND b.Kd_Rek_4 = c.Kd_Rek_4 AND b.Kd_Rek_5 = c.Kd_Rek_5

                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND b.tgl_bukti <= :tgl_1
                                AND IFNULL(c.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(c.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                UNION ALL
                                SELECT a.tahun, a.unit_id, a.kd_potongan, b.no_setoran, b.tgl_setoran, a.keterangan, -(a.nilai) AS nilai
                                FROM ta_setoran_potongan_rinc a
                                INNER JOIN ta_setoran_potongan b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                                INNER JOIN ref_potongan c ON a.kd_potongan = c.kd_potongan
                                    
                                INNER JOIN ta_spj_rinc d ON a.tahun = d.tahun AND a.unit_id = d.unit_id AND a.no_bukti = d.no_bukti
                                
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                                AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5


                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND b.tgl_setoran <= :tgl_1
                                AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            ) a
                            INNER JOIN ref_potongan b ON a.kd_potongan = b.kd_potongan
                            GROUP BY a.tahun, a.unit_id, a.kd_potongan
                            #transaksi Potongan
                            UNION ALL
                            SELECT a.tahun, a.unit_id, a.kd_potongan, b.no_bukti, b.tgl_bukti, b.uraian, a.nilai
                            FROM ta_spj_pot a
                            INNER JOIN ta_spj_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_bukti = b.no_bukti
                            
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) c ON b.tahun = c.tahun AND b.unit_id = c.unit_id AND b.kd_program = c.kd_program AND b.kd_sub_program = c.kd_sub_program AND b.kd_kegiatan = c.kd_kegiatan 
                            AND b.Kd_Rek_1 = c.Kd_Rek_1 AND b.Kd_Rek_2 = c.Kd_Rek_2 AND b.Kd_Rek_3 = c.Kd_Rek_3 AND b.Kd_Rek_4 = c.Kd_Rek_4 AND b.Kd_Rek_5 = c.Kd_Rek_5

                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND b.tgl_bukti <= :tgl_2 AND b.tgl_bukti >= :tgl_1
                            AND IFNULL(c.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(c.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            #transaksi setoran
                            UNION ALL
                            SELECT a.tahun, a.unit_id, a.kd_potongan, b.no_setoran, b.tgl_setoran, a.keterangan, -(a.nilai) AS nilai
                            FROM ta_setoran_potongan_rinc a
                            INNER JOIN ta_setoran_potongan b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                            INNER JOIN ref_potongan c ON a.kd_potongan = c.kd_potongan

                            INNER JOIN ta_spj_rinc d ON a.tahun = d.tahun AND a.unit_id = d.unit_id AND a.no_bukti = d.no_bukti
                            
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                            AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5

                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND b.tgl_setoran <= :tgl_2 AND b.tgl_setoran >= :tgl_1
                            AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                        ) a ORDER BY a.tgl_bukti
                    ", [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':tgl_1' => $this->Tgl_1,
                        ':tgl_2' => $this->Tgl_2,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "                                
                        SELECT * FROM
                        (
                            #Saldo Awal
                            SELECT a.tahun, a.unit_id, a.kd_potongan AS kode, '' AS no_bukti, '$this->tahun-01-01' AS tgl_bukti, CONCAT('Saldo Awal ',b.nm_potongan) AS keterangan, a.nilai
                            FROM ta_saldo_awal_potongan a
                            INNER JOIN ref_potongan b ON a.kd_potongan = b.kd_potongan
                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id
                            AND IFNULL(a.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            #akumulasi transaksi
                            UNION ALL
                            SELECT a.tahun, a.unit_id, a.kd_potongan, '' AS no_bukti, :tgl_1, CONCAT('Saldo Akumulasi ', b.nm_potongan) AS uraian, SUM(nilai) AS nilai
                            FROM (
                                SELECT a.tahun, a.unit_id, a.kd_potongan, b.no_bukti, b.tgl_bukti, b.uraian, a.nilai
                                FROM ta_spj_pot a
                                INNER JOIN ta_spj_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_bukti = b.no_bukti
                                                                    
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) c ON b.tahun = c.tahun AND b.unit_id = c.unit_id AND b.kd_program = c.kd_program AND b.kd_sub_program = c.kd_sub_program AND b.kd_kegiatan = c.kd_kegiatan 
                                AND b.Kd_Rek_1 = c.Kd_Rek_1 AND b.Kd_Rek_2 = c.Kd_Rek_2 AND b.Kd_Rek_3 = c.Kd_Rek_3 AND b.Kd_Rek_4 = c.Kd_Rek_4 AND b.Kd_Rek_5 = c.Kd_Rek_5

                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND b.tgl_bukti < :tgl_1
                                AND IFNULL(c.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(c.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                UNION ALL
                                SELECT a.tahun, a.unit_id, a.kd_potongan, b.no_setoran, b.tgl_setoran, a.keterangan, -(a.nilai) AS nilai
                                FROM ta_setoran_potongan_rinc a
                                INNER JOIN ta_setoran_potongan b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                                INNER JOIN ref_potongan c ON a.kd_potongan = c.kd_potongan
                                    
                                INNER JOIN ta_spj_rinc d ON a.tahun = d.tahun AND a.unit_id = d.unit_id AND a.no_bukti = d.no_bukti
                                
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                    AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                                AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5


                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND b.tgl_setoran < :tgl_1
                                AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            ) a
                            INNER JOIN ref_potongan b ON a.kd_potongan = b.kd_potongan
                            GROUP BY a.tahun, a.unit_id, a.kd_potongan
                            #transaksi Potongan
                            UNION ALL
                            SELECT a.tahun, a.unit_id, a.kd_potongan, b.no_bukti, b.tgl_bukti, b.uraian, a.nilai
                            FROM ta_spj_pot a
                            INNER JOIN ta_spj_rinc b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_bukti = b.no_bukti
                            
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) c ON b.tahun = c.tahun AND b.unit_id = c.unit_id AND b.kd_program = c.kd_program AND b.kd_sub_program = c.kd_sub_program AND b.kd_kegiatan = c.kd_kegiatan 
                            AND b.Kd_Rek_1 = c.Kd_Rek_1 AND b.Kd_Rek_2 = c.Kd_Rek_2 AND b.Kd_Rek_3 = c.Kd_Rek_3 AND b.Kd_Rek_4 = c.Kd_Rek_4 AND b.Kd_Rek_5 = c.Kd_Rek_5

                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND b.tgl_bukti <= :tgl_2 AND b.tgl_bukti >= :tgl_1
                            AND IFNULL(c.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(c.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                            #transaksi setoran
                            UNION ALL
                            SELECT a.tahun, a.unit_id, a.kd_potongan, b.no_setoran, b.tgl_setoran, a.keterangan, -(a.nilai) AS nilai
                            FROM ta_setoran_potongan_rinc a
                            INNER JOIN ta_setoran_potongan b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.no_setoran = b.no_setoran
                            INNER JOIN ref_potongan c ON a.kd_potongan = c.kd_potongan

                            INNER JOIN ta_spj_rinc d ON a.tahun = d.tahun AND a.unit_id = d.unit_id AND a.no_bukti = d.no_bukti
                            
                            LEFT JOIN
                            (
                                SELECT 
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                FROM ta_rkas_history a 
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = :unit_id)
                                AND IFNULL(a.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(a.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                            ) e ON d.tahun = e.tahun AND d.unit_id = e.unit_id AND d.kd_program = e.kd_program AND d.kd_sub_program = e.kd_sub_program AND d.kd_kegiatan = e.kd_kegiatan 
                            AND d.Kd_Rek_1 = e.Kd_Rek_1 AND d.Kd_Rek_2 = e.Kd_Rek_2 AND d.Kd_Rek_3 = e.Kd_Rek_3 AND d.Kd_Rek_4 = e.Kd_Rek_4 AND d.Kd_Rek_5 = e.Kd_Rek_5

                            WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND b.tgl_setoran <= :tgl_2 AND b.tgl_setoran >= :tgl_1
                            AND IFNULL(e.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                        ) a ORDER BY a.tgl_bukti  
                            ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':tgl_1' => $this->Tgl_1,
                        ':tgl_2' => $this->Tgl_2,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);   

                break;
            case 10:
                $totalCount = Yii::$app->db->createCommand("
                        SELECT COUNT(a.kd_program) FROM
                        (
                            SELECT a.kd_program, b.uraian_program, a.kd_sub_program, c.uraian_sub_program, a.kd_kegiatan, d.uraian_kegiatan,
                            a.Kd_Rek_1, j.Nm_Rek_1, a.Kd_Rek_2, e.Nm_Rek_2, a.Kd_Rek_3, f.Nm_Rek_3, a.Kd_Rek_4, g.Nm_Rek_4, a.Kd_Rek_5, h.Nm_Rek_5,
                            a.unit_id, i.nama_unit, a.keterangan,  a.jml_satuan, a.satuan123, a.nilai_rp, SUM(a.total) AS total
                            FROM (
                                SELECT c.tahun, c.unit_id, c.no_peraturan, c.tgl_peraturan, c.perubahan_id, d.kd_program, d.kd_sub_program, d.kd_kegiatan,
                                d.Kd_Rek_1, d.Kd_Rek_2, d.Kd_Rek_3, d.Kd_Rek_4, d.Kd_Rek_5, d.no_rinc, d.keterangan, d.satuan123, d.jml_satuan,
                                d.nilai_rp, d.total
                                FROM
                                ta_rkas_peraturan AS c
                                INNER JOIN ta_rkas_history AS d ON d.tahun = c.tahun AND d.unit_id = c.unit_id AND d.perubahan_id = c.perubahan_id
                                WHERE c.tahun = :tahun AND c.unit_id = :unit_id AND c.perubahan_id = :perubahan_id AND
                                IFNULL(d.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(d.kd_penerimaan_2, '') LIKE :kd_penerimaan_2
                                /*
                                UNION ALL
                                -- SALDO AWAL
                                SELECT
                                a.tahun, :unit_id AS unit_id,
                                0 AS no_peraturan,
                                '' AS tgl_peraturan,
                                :perubahan_id AS perubahan_id,
                                0 AS kd_program,
                                0 AS kd_sub_program,
                                0 AS kd_kegiatan,
                                4 AS Kd_Rek_1,
                                1 AS Kd_Rek_2,
                                4 AS Kd_Rek_3,
                                1 AS Kd_Rek_4,
                                1 AS Kd_Rek_5,
                                0 AS no_rinc,
                                'SALDO AWAL' AS keterangan,
                                1 AS satuan123,
                                0 AS jml_satuan,
                                0 AS nilai_rp,
                                (a.nilai_sa - a.potongan) total
                                FROM
                                (
                                    SELECT a.tahun, SUM(a.nilai) AS nilai_sa, SUM(c.nilai) AS potongan
                                    FROM ta_saldo_awal a
                                    LEFT JOIN ta_saldo_awal_potongan c ON a.tahun = c.tahun AND a.unit_id = c.unit_id AND a.kd_penerimaan_1 = c.kd_penerimaan_1 AND a.kd_penerimaan_2 = c.kd_penerimaan_2
                                    INNER JOIN ref_penerimaan_sisa b ON a.kd_penerimaan_1 = b.penerimaan_sisa_1 AND a.kd_penerimaan_2 = b.penerimaan_sisa_2
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND b.kd_penerimaan_1 LIKE :kd_penerimaan_1 AND b.kd_penerimaan_2 LIKE :kd_penerimaan_2
                                    GROUP BY a.tahun
                                ) a
                                */
                                UNION ALL
                                SELECT c.tahun, c.unit_id, c.no_peraturan, c.tgl_peraturan, c.perubahan_id, d.kd_program, d.kd_sub_program, d.kd_kegiatan,
                                d.Kd_Rek_1, d.Kd_Rek_2, d.Kd_Rek_3, d.Kd_Rek_4, d.Kd_Rek_5, d.no_rinc, d.keterangan, d.satuan123,d.jml_satuan,
                                d.nilai_rp, d.total
                                FROM
                                ta_rkas_peraturan AS c
                                INNER JOIN ta_rkas_history AS d ON d.tahun = c.tahun AND d.unit_id = c.unit_id AND d.perubahan_id = c.perubahan_id
                                INNER JOIN ref_penerimaan_sisa e ON d.kd_penerimaan_1 = e.penerimaan_sisa_1 AND d.kd_penerimaan_2 = e.penerimaan_sisa_2
                                WHERE c.tahun = :tahun AND c.unit_id = :unit_id AND c.perubahan_id = :perubahan_id AND
                                d.kd_penerimaan_1 LIKE 1 AND IFNULL(e.kd_penerimaan_1, '') LIKE :kd_penerimaan_1 AND IFNULL(e.kd_penerimaan_2, '') LIKE :kd_penerimaan_2 AND 
                                CASE 
                                    WHEN :kd_penerimaan_1 = 1 THEN 1=0
                                    WHEN :kd_penerimaan_1 = 0 THEN 1=0
                                    ELSE 1=1
                                END
                            ) a
                            INNER JOIN ref_program b ON a.kd_program = b.kd_program
                            INNER JOIN ref_sub_program c ON a.kd_program = c.kd_program AND a.kd_sub_program = c.kd_sub_program
                            INNER JOIN ref_kegiatan d ON a.kd_program = d.kd_program AND a.kd_sub_program = d.kd_sub_program AND a.kd_kegiatan = d.kd_kegiatan
                            INNER JOIN ref_rek_1 j ON a.Kd_Rek_1 = j.Kd_Rek_1
                            INNER JOIN ref_rek_2 e ON a.Kd_Rek_1 = e.Kd_Rek_1 AND a.Kd_Rek_2 =  e.Kd_Rek_2
                            INNER JOIN ref_rek_3 f ON a.Kd_Rek_1 = f.Kd_Rek_1 AND a.Kd_Rek_2 =  f.Kd_Rek_2 AND a.Kd_Rek_3 = f.Kd_Rek_3
                            INNER JOIN ref_rek_4 g ON a.Kd_Rek_1 = g.Kd_Rek_1 AND a.Kd_Rek_2 =  g.Kd_Rek_2 AND a.Kd_Rek_3 = g.Kd_Rek_3 AND a.Kd_Rek_4 = g.Kd_Rek_4
                            INNER JOIN ref_rek_5 h ON a.Kd_Rek_1 = h.Kd_Rek_1 AND a.Kd_Rek_2 =  h.Kd_Rek_2 AND a.Kd_Rek_3 = h.Kd_Rek_3 AND a.Kd_Rek_4 = h.Kd_Rek_4 AND a.Kd_Rek_5 = h.Kd_Rek_5
                            INNER JOIN ref_unit i ON a.unit_id = i.id
                            GROUP BY a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.jml_satuan, a.satuan123, a.nilai_rp, a.keterangan
                            ORDER BY a.Kd_Rek_1, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5 ASC                         
                        ) a
                    ", [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "
                        SELECT a.kd_program, a.uraian_program, a.kd_sub_program, a.uraian_sub_program, a.kd_kegiatan, a.uraian_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2, k.abbr,
                        a.Kd_Rek_1, j.Nm_Rek_1, a.Kd_Rek_2, e.Nm_Rek_2, a.Kd_Rek_3, f.Nm_Rek_3, a.Kd_Rek_4, g.Nm_Rek_4, a.Kd_Rek_5, h.Nm_Rek_5,
                        a.unit_id, i.nama_unit, a.keterangan,
                        CASE
                            WHEN a.satuan123 = 0 THEN a.satuan123_sebelum 
                            ELSE a.satuan123
                        END AS satuan123,
                        SUM(a.jml_satuan) AS jml_satuan,  SUM(a.nilai_rp) AS nilai_rp, SUM(a.total) AS total,
                        CASE
                            WHEN a.satuan123_sebelum = 0 THEN a.satuan123 
                            ELSE a.satuan123_sebelum
                        END AS satuan123_sebelum,
                        SUM(a.jml_satuan_sebelum) AS jml_satuan_sebelum,  SUM(a.nilai_rp_sebelum) AS nilai_rp_sebelum, SUM(a.total_sebelum) AS total_sebelum
                        FROM
                        (
                            SELECT
                            c.tahun, c.unit_id, a.uraian_program , d.uraian_sub_program, e.uraian_kegiatan,
                            c.kd_program, c.kd_sub_program, c.kd_kegiatan, c.Kd_Rek_1, c.Kd_Rek_2, c.Kd_Rek_3, c.Kd_Rek_4, c.Kd_Rek_5,
                            c.no_rinc, c.keterangan, c.satuan123, c.jml_satuan, c.nilai_rp, c.total, 0 AS satuan123_sebelum, 0 AS jml_satuan_sebelum, 0 AS nilai_rp_sebelum, 0 AS total_sebelum, c.kd_penerimaan_1, c.kd_penerimaan_2
                            FROM ref_program a
                            INNER JOIN ref_sub_program d ON a.kd_program = d.kd_program
                            INNER JOIN ta_rkas_history c ON c.kd_program = d.kd_program AND c.kd_sub_program = d.kd_sub_program
                            INNER JOIN ref_kegiatan e ON c.kd_program = e.kd_program AND c.kd_sub_program = e.kd_sub_program AND c.kd_kegiatan = e.kd_kegiatan
                            WHERE
                            c.tahun = :tahun AND
                            c.unit_id = :unit_id AND c.perubahan_id = :perubahan_id AND IFNULL(c.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(c.kd_penerimaan_2,'') LIKE :kd_penerimaan_2
                            
                            UNION ALL
                            SELECT
                            c.tahun, c.unit_id, a.uraian_program , d.uraian_sub_program, e.uraian_kegiatan,
                            c.kd_program, c.kd_sub_program, c.kd_kegiatan, c.Kd_Rek_1, c.Kd_Rek_2, c.Kd_Rek_3, c.Kd_Rek_4, c.Kd_Rek_5,
                            c.no_rinc, c.keterangan AS keterangan_sebelum, 0 AS satuan123, 0 AS jml_satuan, 0 AS nilai_rp, 0 AS total,
                            c.satuan123 AS satuan123_sebelum, c.jml_satuan AS jml_satuan_sebelum, c.nilai_rp AS nilai_rp_sebelum, c.total AS total_sebelum, 
                            c.kd_penerimaan_1, c.kd_penerimaan_2
                            FROM ref_program a
                            INNER JOIN ref_sub_program d ON a.kd_program = d.kd_program
                            INNER JOIN ta_rkas_history c ON c.kd_program = d.kd_program AND c.kd_sub_program = d.kd_sub_program
                            INNER JOIN ref_kegiatan e ON c.kd_program = e.kd_program AND c.kd_sub_program = e.kd_sub_program AND c.kd_kegiatan = e.kd_kegiatan
                            WHERE
                            c.tahun = :tahun AND
                            c.unit_id = :unit_id AND c.perubahan_id = 4 AND IFNULL(c.kd_penerimaan_1,'') LIKE :kd_penerimaan_1 AND IFNULL(c.kd_penerimaan_2,'') LIKE :kd_penerimaan_2
                        ) a
                        INNER JOIN ref_rek_1 j ON a.Kd_Rek_1 = j.Kd_Rek_1
                        INNER JOIN ref_rek_2 e ON a.Kd_Rek_1 = e.Kd_Rek_1 AND a.Kd_Rek_2 =  e.Kd_Rek_2
                        INNER JOIN ref_rek_3 f ON a.Kd_Rek_1 = f.Kd_Rek_1 AND a.Kd_Rek_2 =  f.Kd_Rek_2 AND a.Kd_Rek_3 = f.Kd_Rek_3
                        INNER JOIN ref_rek_4 g ON a.Kd_Rek_1 = g.Kd_Rek_1 AND a.Kd_Rek_2 =  g.Kd_Rek_2 AND a.Kd_Rek_3 = g.Kd_Rek_3 AND a.Kd_Rek_4 = g.Kd_Rek_4
                        INNER JOIN ref_rek_5 h ON a.Kd_Rek_1 = h.Kd_Rek_1 AND a.Kd_Rek_2 =  h.Kd_Rek_2 AND a.Kd_Rek_3 = h.Kd_Rek_3 AND a.Kd_Rek_4 = h.Kd_Rek_4 AND a.Kd_Rek_5 = h.Kd_Rek_5
                        INNER JOIN ref_unit i ON a.unit_id = i.id
                        INNER JOIN ref_penerimaan_2 k ON a.kd_penerimaan_1 = k.kd_penerimaan_1 AND a.kd_penerimaan_2 = k.kd_penerimaan_2
                        GROUP BY a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.no_rinc
                        ORDER BY a.Kd_Rek_1, a.kd_program, a.kd_kegiatan, a.kd_penerimaan_1, a.kd_penerimaan_2, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5 ASC                                                                            
                    ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        ':unit_id' => $this->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);  

                              
            default:
                # code...
                break;
        }

        return $data;
    }



}
