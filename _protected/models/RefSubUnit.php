<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ref_sub_unit".
 *
 * @property int $unit_id
 * @property int $sub_unit_id
 * @property string $nama_sub_unit
 *
 * @property RefUnit $unit
 * @property User[] $users
 */
class RefSubUnit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ref_sub_unit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unit_id', 'sub_unit_id'], 'required'],
            [['unit_id', 'sub_unit_id'], 'default', 'value' => null],
            [['unit_id', 'sub_unit_id'], 'integer'],
            [['nama_sub_unit'], 'string', 'max' => 100],
            [['unit_id', 'sub_unit_id'], 'unique', 'targetAttribute' => ['unit_id', 'sub_unit_id']],
            [['unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => RefUnit::className(), 'targetAttribute' => ['unit_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'unit_id' => 'Unit ID',
            'sub_unit_id' => 'Sub Unit ID',
            'nama_sub_unit' => 'Nama Sub Unit',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnit()
    {
        return $this->hasOne(RefUnit::className(), ['id' => 'unit_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['unit_id' => 'unit_id', 'sub_unit_id' => 'sub_unit_id']);
    }
}
