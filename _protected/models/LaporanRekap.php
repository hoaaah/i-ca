<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;

/**
 * TaValidasiPembayaranSearch represents the model behind the search form about `app\models\TaValidasiPembayaran`.
 */
class LaporanRekap extends Laporan
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
            '1' => 'Rekapitulasi Penetapan RUK/RKA',
            '7' => 'Rekapitulasi Penetapan RUK/RKA - Display',
            '2' => 'Rekapitulasi RUK/RKA',               
            '3' => 'Rekapitulasi Pembuatan SPJ',
            // '4' => 'Rekapitulasi Realisasi Pendapatan dan Belanja',
            '5' => 'Rekapitulasi SP3B dan SP2B',
            '6' => 'Rekapitulasi Sisa dana (Saldo Awal)',
        ];
    }

    public function getRender()
    {
        $render = null;
        switch ($this->Kd_Laporan) {
            case 4:
                $render = 'laporan6';
                break;
            case 6:
                $render = 'laporan61';
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
                        SELECT
                        COUNT(b.id)
                        FROM
                        ref_unit AS b
                        LEFT JOIN (SELECT * FROM ta_rkas_peraturan a WHERE a.tahun = :tahun AND a.perubahan_id LIKE :perubahan_id) AS a ON a.unit_id = b.id
                    ", [
                        ':tahun' => $this->tahun,
                        // ':pendidikan_id' => $this->pendidikan_id,
                        ':perubahan_id' => $this->perubahan_id,
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "
                        SELECT
                        a.tahun,
                        a.unit_id,
                        b.nama_unit,
                        a.no_peraturan,
                        a.tgl_peraturan,
                        a.verifikasi
                        FROM
                        ref_unit AS b
                        LEFT JOIN (SELECT * FROM ta_rkas_peraturan a WHERE a.tahun = :tahun AND a.perubahan_id LIKE :perubahan_id) AS a ON a.unit_id = b.id
                        ORDER BY a.unit_id, a.perubahan_id, a.tgl_peraturan ASC
                            ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        // ':pendidikan_id' => $this->pendidikan_id,
                        ':perubahan_id' => $this->perubahan_id,
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);                        
                $render = 'laporan1';
                break;
            case 2:
                $totalCount = Yii::$app->db->createCommand("
                        SELECT COUNT(a.tahun) FROM
                        (
                            SELECT
                            a.tahun, c.nama_unit, a.unit_id, a.perubahan_id, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5,
                            j.Nm_Rek_1, i.Nm_Rek_2, h.Nm_Rek_3, g.Nm_Rek_4, f.Nm_Rek_5, SUM(a.total) AS total
                            FROM ta_rkas_history AS a
                            INNER JOIN ta_rkas_peraturan AS b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.perubahan_id = b.perubahan_id
                            INNER JOIN ref_rek_5 AS f ON a.Kd_Rek_1 = f.Kd_Rek_1 AND a.Kd_Rek_2 = f.Kd_Rek_2 AND a.Kd_Rek_3 = f.Kd_Rek_3 AND a.Kd_Rek_4 = f.Kd_Rek_4 AND a.Kd_Rek_5 = f.Kd_Rek_5
                            INNER JOIN ref_rek_4 AS g ON f.Kd_Rek_1 = g.Kd_Rek_1 AND f.Kd_Rek_2 = g.Kd_Rek_2 AND f.Kd_Rek_3 = g.Kd_Rek_3 AND f.Kd_Rek_4 = g.Kd_Rek_4
                            INNER JOIN ref_rek_3 AS h ON g.Kd_Rek_1 = h.Kd_Rek_1 AND g.Kd_Rek_2 = h.Kd_Rek_2 AND g.Kd_Rek_3 = h.Kd_Rek_3
                            INNER JOIN ref_unit AS c ON b.unit_id = c.id
                            INNER JOIN ref_rek_2 AS i ON h.Kd_Rek_1 = i.Kd_Rek_1 AND h.Kd_Rek_2 = i.Kd_Rek_2
                            INNER JOIN ref_rek_1 AS j ON i.Kd_Rek_1 = j.Kd_Rek_1
                            WHERE a.tahun = :tahun AND a.perubahan_id LIKE :perubahan_id AND a.kd_penerimaan_1 LIKE :kd_penerimaan_1 AND a.kd_penerimaan_2 LIKE :kd_penerimaan_2
                            GROUP BY a.tahun, c.nama_unit, a.unit_id, a.perubahan_id, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5,
                            j.Nm_Rek_1, i.Nm_Rek_2, h.Nm_Rek_3, g.Nm_Rek_4, f.Nm_Rek_5
                            ORDER BY a.unit_id, a.perubahan_id, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5 ASC
                        ) a
                    ", [
                        ':tahun' => $this->tahun,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                        // ':pendidikan_id' => $this->pendidikan_id,
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "
                        SELECT
                        a.tahun, c.nama_unit, a.unit_id, a.perubahan_id, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5,
                        j.Nm_Rek_1, i.Nm_Rek_2, h.Nm_Rek_3, g.Nm_Rek_4, f.Nm_Rek_5, SUM(a.total) AS total
                        FROM ta_rkas_history AS a
                        INNER JOIN ta_rkas_peraturan AS b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.perubahan_id = b.perubahan_id
                        INNER JOIN ref_rek_5 AS f ON a.Kd_Rek_1 = f.Kd_Rek_1 AND a.Kd_Rek_2 = f.Kd_Rek_2 AND a.Kd_Rek_3 = f.Kd_Rek_3 AND a.Kd_Rek_4 = f.Kd_Rek_4 AND a.Kd_Rek_5 = f.Kd_Rek_5
                        INNER JOIN ref_rek_4 AS g ON f.Kd_Rek_1 = g.Kd_Rek_1 AND f.Kd_Rek_2 = g.Kd_Rek_2 AND f.Kd_Rek_3 = g.Kd_Rek_3 AND f.Kd_Rek_4 = g.Kd_Rek_4
                        INNER JOIN ref_rek_3 AS h ON g.Kd_Rek_1 = h.Kd_Rek_1 AND g.Kd_Rek_2 = h.Kd_Rek_2 AND g.Kd_Rek_3 = h.Kd_Rek_3
                        INNER JOIN ref_unit AS c ON b.unit_id = c.id
                        INNER JOIN ref_rek_2 AS i ON h.Kd_Rek_1 = i.Kd_Rek_1 AND h.Kd_Rek_2 = i.Kd_Rek_2
                        INNER JOIN ref_rek_1 AS j ON i.Kd_Rek_1 = j.Kd_Rek_1
                        WHERE a.tahun = :tahun AND a.perubahan_id LIKE :perubahan_id AND a.kd_penerimaan_1 LIKE :kd_penerimaan_1 AND a.kd_penerimaan_2 LIKE :kd_penerimaan_2
                        GROUP BY a.tahun, c.nama_unit, a.unit_id, a.perubahan_id, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5,
                        j.Nm_Rek_1, i.Nm_Rek_2, h.Nm_Rek_3, g.Nm_Rek_4, f.Nm_Rek_5
                        ORDER BY a.unit_id, a.perubahan_id, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5 ASC
                    ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                        // ':pendidikan_id' => $this->pendidikan_id,
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);                                  
                $render = 'laporan2';
                break;   
            case 3:
                $totalCount = Yii::$app->db->createCommand("
                        SELECT COUNT(a.nama_unit) FROM
                        (
                            SELECT
                            b.nama_unit,
                            a.tahun,
                            a.unit_id,
                            a.no_spj,
                            a.tgl_spj,
                            a.keterangan
                            FROM ref_unit AS b
                            LEFT JOIN (SELECT * FROM ta_spj WHERE tahun = :tahun AND tgl_spj >= :Tgl_1 AND tgl_spj <= :Tgl_2  AND kd_sah = 2) a ON a.unit_id = b.id
                        ) a
                    ", [
                        ':tahun' => $this->tahun,
                        ':Tgl_1' => $this->Tgl_1,
                        ':Tgl_2' => $this->Tgl_2,
                        // ':pendidikan_id' => $this->pendidikan_id,
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "
                        SELECT
                        b.nama_unit,
                        a.tahun,
                        a.unit_id,
                        a.no_spj,
                        a.tgl_spj,
                        a.keterangan
                        FROM ref_unit AS b
                        LEFT JOIN (SELECT * FROM ta_spj WHERE tahun = :tahun AND tgl_spj >= :Tgl_1 AND tgl_spj <= :Tgl_2  AND kd_sah = 2) a ON a.unit_id = b.id
                    ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        ':Tgl_1' => $this->Tgl_1,
                        ':Tgl_2' => $this->Tgl_2,
                        // ':pendidikan_id' => $this->pendidikan_id,
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);                                  
                $render = 'laporan3';
                break;                                                 
            case 4:
                $totalCount = Yii::$app->db->createCommand("
                    SELECT COUNT(a.tahun) FROM
                    (   
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
                        ':unit_id' => Yii::$app->user->identity->unit_id,
                        ':perubahan_id' => $this->perubahan_id,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2,
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "

                            SELECT a.tahun, a.unit_id, a.kd_program, c.uraian_program, a.kd_sub_program, d.uraian_sub_program, a.kd_kegiatan, e.uraian_kegiatan, a.Kd_Rek_1, a.anggaran,
                            IFNULL(f.nilai,0) AS rutin, IFNULL(g.nilai,0) AS bos_pusat, IFNULL(j.nilai,0) AS bos_provinsi, IFNULL(k.nilai,0) AS bos_lain, IFNULL(h.nilai,0) AS bantuan, IFNULL(i.nilai,0) AS lain
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
                            -- Untuk realisasi Rutin 2
                            LEFT JOIN
                            (
                                SELECT
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, SUM(a.nilai) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = 1)
                                    AND a.kd_penerimaan_1 = 2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND b.kd_penerimaan_1 = 2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1
                            ) f ON a.tahun = f.tahun AND a.unit_id = f.unit_id AND a.kd_program = f.kd_program AND a.kd_sub_program = f.kd_sub_program AND a.kd_kegiatan = f.kd_kegiatan AND a.Kd_Rek_1 = f.Kd_Rek_1
                            -- Untuk realisasi BOS pusat 3-1
                            LEFT JOIN
                            (
                                SELECT
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, SUM(a.nilai) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = 1)
                                    AND a.kd_penerimaan_1 = 3 AND a.kd_penerimaan_2 = 1
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND b.kd_penerimaan_1 = 3 AND b.kd_penerimaan_2 = 1
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1
                            ) g ON a.tahun = g.tahun AND a.unit_id = g.unit_id AND a.kd_program = g.kd_program AND a.kd_sub_program = g.kd_sub_program AND a.kd_kegiatan = g.kd_kegiatan AND a.Kd_Rek_1 = g.Kd_Rek_1
                            -- Untuk realisasi bantuan 4
                            LEFT JOIN
                            (
                                SELECT
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, SUM(a.nilai) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = 1)
                                    AND a.kd_penerimaan_1 = 4
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND b.kd_penerimaan_1 = 4
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1
                            ) h ON a.tahun = h.tahun AND a.unit_id = h.unit_id AND a.kd_program = h.kd_program AND a.kd_sub_program = h.kd_sub_program AND a.kd_kegiatan = h.kd_kegiatan AND a.Kd_Rek_1 = h.Kd_Rek_1
                            -- untuk realisasi sumber lainnya 5
                            LEFT JOIN
                            (
                                SELECT
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, SUM(a.nilai) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = 1)
                                    AND a.kd_penerimaan_1 NOT IN (1,2,3,4)
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND b.kd_penerimaan_1 NOT IN (1,2,3,4)
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1
                            ) i ON a.tahun = i.tahun AND a.unit_id = i.unit_id AND a.kd_program = i.kd_program AND a.kd_sub_program = i.kd_sub_program AND a.kd_kegiatan = i.kd_kegiatan AND a.Kd_Rek_1 = i.Kd_Rek_1
                            -- Untuk realisasi BOS provinsi 3-2
                            LEFT JOIN
                            (
                                SELECT
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, SUM(a.nilai) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = 1)
                                    AND a.kd_penerimaan_1 = 3 AND a.kd_penerimaan_2 = 2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND b.kd_penerimaan_1 = 3 AND b.kd_penerimaan_2 = 2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1
                            ) j ON a.tahun = j.tahun AND a.unit_id = j.unit_id AND a.kd_program = j.kd_program AND a.kd_sub_program = j.kd_sub_program AND a.kd_kegiatan = j.kd_kegiatan AND a.Kd_Rek_1 = j.Kd_Rek_1
                            -- Untuk realisasi BOS kab/kota 3-x
                            LEFT JOIN
                            (
                                SELECT
                                a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, SUM(a.nilai) AS nilai
                                FROM
                                ta_spj_rinc AS a
                                LEFT JOIN
                                (
                                    SELECT 
                                    a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                    FROM ta_rkas_history a 
                                    WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.perubahan_id = (SELECT MAX(perubahan_id) FROM ta_rkas_peraturan WHERE tahun = :tahun AND unit_id = 1)
                                    AND a.kd_penerimaan_1 = 3 AND a.kd_penerimaan_2 > 2
                                    GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1, a.Kd_Rek_2, a.Kd_Rek_3, a.Kd_Rek_4, a.Kd_Rek_5, a.kd_penerimaan_1, a.kd_penerimaan_2
                                ) b ON a.tahun = b.tahun AND a.unit_id = b.unit_id AND a.kd_program = b.kd_program AND a.kd_sub_program = b.kd_sub_program AND a.kd_kegiatan = b.kd_kegiatan 
                                AND a.Kd_Rek_1 = b.Kd_Rek_1 AND a.Kd_Rek_2 = b.Kd_Rek_2 AND a.Kd_Rek_3 = b.Kd_Rek_3 AND a.Kd_Rek_4 = b.Kd_Rek_4 AND a.Kd_Rek_5 = b.Kd_Rek_5
                                WHERE a.tahun = :tahun AND a.unit_id = :unit_id AND a.tgl_bukti <= :tgl_2 AND a.tgl_bukti >= :tgl_1 AND b.kd_penerimaan_1 = 3 AND b.kd_penerimaan_2 > 2
                                GROUP BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1
                            ) k ON a.tahun = k.tahun AND a.unit_id = k.unit_id AND a.kd_program = k.kd_program AND a.kd_sub_program = k.kd_sub_program AND a.kd_kegiatan = k.kd_kegiatan AND a.Kd_Rek_1 = k.Kd_Rek_1
                            LEFT JOIN ref_program c ON a.kd_program = c.kd_program
                            LEFT JOIN ref_sub_program d ON a.kd_program = d.kd_program AND a.kd_sub_program = d.kd_sub_program
                            LEFT JOIN ref_kegiatan e ON a.kd_program = e.kd_program AND a.kd_sub_program = e.kd_sub_program AND a.kd_kegiatan = e.kd_kegiatan
                            ORDER BY a.tahun, a.unit_id, a.kd_program, a.kd_sub_program, a.kd_kegiatan, a.Kd_Rek_1 ASC;

                            ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        ':unit_id' => Yii::$app->user->identity->unit_id,
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

                $render = 'laporan6';
                break;   
                            
            case 5:
                $totalCount = Yii::$app->db->createCommand("
                        SELECT COUNT(a.tahun) AS tahun
                        FROM ta_sp3b a
                        WHERE a.tahun = :tahun AND a.status = 2 AND a.tgl_sp3b <= :tgl_2 AND a.tgl_sp3b >= :tgl_1
                    ", [
                        ':tahun' => $this->tahun,
                        ':tgl_1' => $this->Tgl_1,
                        ':tgl_2' => $this->Tgl_2,
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "
                        SELECT a.no_sp3b, a.tgl_sp3b, b.no_sp2b, b.tgl_sp2b, a.keterangan
                        FROM ta_sp3b a 
                        LEFT JOIN ta_sp2b b ON a.tahun = b.tahun AND a.no_sp3b = b.no_sp3b
                        WHERE a.tahun = :tahun AND a.status = 2 AND a.tgl_sp3b <= :tgl_2 AND a.tgl_sp3b >= :tgl_1 
                            ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        ':tgl_1' => $this->Tgl_1,
                        ':tgl_2' => $this->Tgl_2,
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);    
                $render = 'laporan5';
                break;
            case 6:
                $totalCount = Yii::$app->db->createCommand("
                    SELECT COUNT(a.unit_id) FROM(
                        SELECT a.unit_id, a.nama_unit, SUM(a.bank) AS bank, SUM(a.tunai) AS tunai
                        FROM
                        (
                            SELECT a.unit_id, b.nama_unit,
                            CASE
                                WHEN pembayaran = 1 THEN nilai
                                ELSE 0
                            END AS bank,
                            CASE
                                WHEN pembayaran = 2 THEN nilai
                                ELSE 0
                            END AS tunai
                        
                            FROM ta_saldo_awal a
                            INNER JOIN ref_unit b ON a.unit_id = b.id
                            WHERE a.tahun = :tahun AND a.kd_penerimaan_1 LIKE :kd_penerimaan_1 AND a.kd_penerimaan_2 LIKE :kd_penerimaan_2
                        ) a GROUP BY a.unit_id, a.nama_unit ORDER BY a.nama_unit ASC 
                    )a
                ", [
                    ':tahun' => $this->tahun,
                    ':kd_penerimaan_1' => $kd_penerimaan_1,
                    ':kd_penerimaan_2' => $kd_penerimaan_2
                ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "
                        SELECT a.unit_id, a.nama_unit, SUM(a.bank) AS bank, SUM(a.tunai) AS tunai
                        FROM
                        (
                            SELECT a.unit_id, b.nama_unit,
                            CASE
                                WHEN pembayaran = 1 THEN nilai
                                ELSE 0
                            END AS bank,
                            CASE
                                WHEN pembayaran = 2 THEN nilai
                                ELSE 0
                            END AS tunai
                        
                            FROM ta_saldo_awal a
                            INNER JOIN ref_unit b ON a.unit_id = b.id
                            WHERE a.tahun = :tahun AND a.kd_penerimaan_1 LIKE :kd_penerimaan_1 AND a.kd_penerimaan_2 LIKE :kd_penerimaan_2
                        ) a GROUP BY a.unit_id, a.nama_unit ORDER BY a.nama_unit ASC 
                    ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        ':kd_penerimaan_1' => $kd_penerimaan_1,
                        ':kd_penerimaan_2' => $kd_penerimaan_2
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);    
                $render = 'laporan61';
                break;
            case 7:
                $totalCount = Yii::$app->db->createCommand("
                            SELECT 
                            COUNT(a.id)
                            FROM ref_unit a
                    ", [
                        // ':pendidikan_id' => $this->pendidikan_id,
                    ])->queryScalar();

                $data = new SqlDataProvider([
                    'sql' => "
                        SELECT
                        a.id, a.nama_unit,
                        b.no_peraturan AS perubahan1,
                        c.no_peraturan AS perubahan2,
                        d.no_peraturan AS perubahan3,
                        e.no_peraturan AS perubahan4,
                        f.no_peraturan AS perubahan5,
                        g.no_peraturan AS perubahan6
                        FROM
                        (
                            SELECT 
                            a.id, a.nama_unit
                            FROM ref_unit a
                        ) a 
                        LEFT JOIN
                        (
                            SELECT unit_id, perubahan_id, no_peraturan FROM ta_rkas_peraturan WHERE tahun = :tahun AND perubahan_id = 1
                        ) b ON a.id = b.unit_id
                        LEFT JOIN
                        (
                            SELECT unit_id, perubahan_id, no_peraturan FROM ta_rkas_peraturan WHERE tahun = :tahun AND perubahan_id = 2
                        ) c ON a.id = c.unit_id
                        LEFT JOIN
                        (
                            SELECT unit_id, perubahan_id, no_peraturan FROM ta_rkas_peraturan WHERE tahun = :tahun AND perubahan_id = 3
                        ) d ON a.id = d.unit_id
                        LEFT JOIN
                        (
                            SELECT unit_id, perubahan_id, no_peraturan FROM ta_rkas_peraturan WHERE tahun = :tahun AND perubahan_id = 4
                        ) e ON a.id = e.unit_id
                        LEFT JOIN
                        (
                            SELECT unit_id, perubahan_id, no_peraturan FROM ta_rkas_peraturan WHERE tahun = :tahun AND perubahan_id = 5
                        ) f ON a.id = f.unit_id
                        LEFT JOIN
                        (
                            SELECT unit_id, perubahan_id, no_peraturan FROM ta_rkas_peraturan WHERE tahun = :tahun AND perubahan_id = 6
                        ) g ON a.id = g.unit_id
                    ",
                    'params' => [
                        ':tahun' => $this->tahun,
                        // ':pendidikan_id' => $this->pendidikan_id,
                    ],
                    'totalCount' => $totalCount,
                    //'sort' =>false, to remove the table header sorting
                    'pagination' => [
                        'pageSize' => 50,
                    ],
                ]);                        
                $render = 'laporan7';
                break;                        
            default:
                # code...
                break;
        }

        return $data;
    }



}
