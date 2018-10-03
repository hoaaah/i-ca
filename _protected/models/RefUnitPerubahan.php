<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "ref_unit_perubahan".
 *
 * @property integer $id
 * @property integer $unit_id
 * @property string $nama_unit
 * @property string $alamat
 * @property string $kepala_unit
 * @property string $nip
 * @property string $rekening_bank
 * @property string $nama_bank
 * @property string $alamat_cabang
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $user_id
 */
class RefUnitPerubahan extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ref_unit_perubahan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['unit_id', 'created_at', 'updated_at', 'user_id'], 'integer'],
            [['nama_unit'], 'string', 'max' => 100],
            [['alamat', 'kepala_unit', 'rekening_bank', 'nama_bank', 'alamat_cabang'], 'string', 'max' => 255],
            [['nip'], 'string', 'max' => 18],
            [['no_induk'], 'string', 'max' => 12],
            [['unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => RefUnit::className(), 'targetAttribute' => ['unit_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'unit_id' => Yii::t('app', 'Sekolah ID'),
            'nama_unit' => Yii::t('app', 'Nama Sekolah'),
            'alamat' => Yii::t('app', 'Alamat'),
            'kepala_unit' => Yii::t('app', 'Kepala Sekolah'),
            'nip' => Yii::t('app', 'Nip'),
            'rekening_bank' => Yii::t('app', 'Rekening Sekolah'),
            'nama_bank' => Yii::t('app', 'Nama Bank'),
            'alamat_cabang' => Yii::t('app', 'Alamat Cabang'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'user_id' => Yii::t('app', 'User ID'),
            'no_induk' => 'no_induk',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => 'user_id',
            ],            
        ];
    }     
}
