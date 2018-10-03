<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ref_unit".
 *
 * @property int $id
 * @property int $jenis_id
 * @property string $nama_unit
 * @property string $alamat
 * @property string $kepala_unit
 * @property string $nip
 * @property string $no_induk
 *
 * @property RefSubUnit[] $refSubUnits
 * @property RefJenisUnit $jenis
 * @property User[] $users
 */
class RefUnit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ref_unit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['jenis_id'], 'required'],
            [['jenis_id'], 'default', 'value' => null],
            [['jenis_id'], 'integer'],
            [['nama_unit', 'alamat'], 'string', 'max' => 255],
            [['kepala_unit'], 'string', 'max' => 100],
            [['nip'], 'string', 'max' => 18],
            [['no_induk'], 'string', 'max' => 14],
            [['jenis_id'], 'exist', 'skipOnError' => true, 'targetClass' => RefJenisUnit::className(), 'targetAttribute' => ['jenis_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'jenis_id' => 'Jenis ID',
            'nama_unit' => 'Nama Unit',
            'alamat' => 'Alamat',
            'kepala_unit' => 'Kepala Unit',
            'nip' => 'Nip',
            'no_induk' => 'No Induk',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRefSubUnits()
    {
        return $this->hasMany(RefSubUnit::className(), ['unit_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJenis()
    {
        return $this->hasOne(RefJenisUnit::className(), ['id' => 'jenis_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['unit_id' => 'id']);
    }
}
