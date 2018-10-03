<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "T_CS".
 *
 * @property string $cs_id
 * @property string $id_group
 * @property int $cs_n
 * @property string $cs_no
 * @property string $st_no
 * @property string $st_tgl
 * @property string $maksud_perjalanan
 * @property string $beban_instansi
 * @property string $kode_anggaran
 * @property string $cs_tgl
 * @property string $cs_pengaju_nip
 * @property string $cs_pengaju_nama
 * @property string $cs_pengaju_jabatan
 * @property string $cs_setuju_1_tgl
 * @property string $cs_setuju_1_nip
 * @property string $cs_setuju_1_nama
 * @property string $cs_setuju_1_jabatan
 * @property string $cs_setuju_2_tgl
 * @property string $cs_setuju_2_nip
 * @property string $cs_setuju_2_nama
 * @property string $cs_setuju_2_jabatan
 * @property int $ttd_pengaju
 * @property int $ttd_setuju_1
 * @property int $ttd_setuju_2
 * @property string $cs_anggaran
 * @property string $cs_realisasi_lalu
 * @property string $u_insert
 * @property string $date_insert
 */
class TCs extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'T_CS';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dbcs');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_group'], 'required'],
            [['id_group', 'cs_n', 'ttd_pengaju', 'ttd_setuju_1', 'ttd_setuju_2'], 'integer'],
            [['st_tgl', 'cs_tgl', 'cs_setuju_1_tgl', 'cs_setuju_2_tgl'], 'string'],
            [['cs_anggaran', 'cs_realisasi_lalu'], 'number'],
            [['date_insert'], 'safe'],
            [['cs_no', 'cs_pengaju_nama', 'cs_setuju_1_nama', 'cs_setuju_2_nama'], 'string', 'max' => 80],
            [['st_no', 'kode_anggaran'], 'string', 'max' => 40],
            [['maksud_perjalanan'], 'string', 'max' => 255],
            [['beban_instansi'], 'string', 'max' => 50],
            [['cs_pengaju_nip', 'cs_setuju_1_nip', 'cs_setuju_2_nip'], 'string', 'max' => 21],
            [['cs_pengaju_jabatan', 'cs_setuju_1_jabatan', 'cs_setuju_2_jabatan'], 'string', 'max' => 150],
            [['u_insert'], 'string', 'max' => 31],
            [['id_group'], 'exist', 'skipOnError' => true, 'targetClass' => AGroup::className(), 'targetAttribute' => ['id_group' => 'id_group']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cs_id' => 'Cs ID',
            'id_group' => 'Id Group',
            'cs_n' => 'Cs N',
            'cs_no' => 'Cs No',
            'st_no' => 'St No',
            'st_tgl' => 'St Tgl',
            'maksud_perjalanan' => 'Maksud Perjalanan',
            'beban_instansi' => 'Beban Instansi',
            'kode_anggaran' => 'Kode Anggaran',
            'cs_tgl' => 'Cs Tgl',
            'cs_pengaju_nip' => 'Cs Pengaju Nip',
            'cs_pengaju_nama' => 'Cs Pengaju Nama',
            'cs_pengaju_jabatan' => 'Cs Pengaju Jabatan',
            'cs_setuju_1_tgl' => 'Cs Setuju 1 Tgl',
            'cs_setuju_1_nip' => 'Cs Setuju 1 Nip',
            'cs_setuju_1_nama' => 'Cs Setuju 1 Nama',
            'cs_setuju_1_jabatan' => 'Cs Setuju 1 Jabatan',
            'cs_setuju_2_tgl' => 'Cs Setuju 2 Tgl',
            'cs_setuju_2_nip' => 'Cs Setuju 2 Nip',
            'cs_setuju_2_nama' => 'Cs Setuju 2 Nama',
            'cs_setuju_2_jabatan' => 'Cs Setuju 2 Jabatan',
            'ttd_pengaju' => 'Ttd Pengaju',
            'ttd_setuju_1' => 'Ttd Setuju 1',
            'ttd_setuju_2' => 'Ttd Setuju 2',
            'cs_anggaran' => 'Cs Anggaran',
            'cs_realisasi_lalu' => 'Cs Realisasi Lalu',
            'u_insert' => 'U Insert',
            'date_insert' => 'Date Insert',
        ];
    }
}
