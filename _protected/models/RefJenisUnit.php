<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ref_jenis_unit".
 *
 * @property integer $id
 * @property integer $kategori_unit_id
 * @property string $jenis_unit
 */
class RefJenisUnit extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ref_jenis_unit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['kategori_unit_id'], 'required'],
            [['kategori_unit_id'], 'integer'],
            [['jenis_unit'], 'string', 'max' => 100],
            [['kategori_unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => RefPendidikan::className(), 'targetAttribute' => ['kategori_unit_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'kategori_unit_id' => 'FKTP ID',
            'jenis_unit' => 'Jenis Puskesmas',
        ];
    }
}
