<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ref_user_parent".
 *
 * @property int $child_id
 * @property int $parent_id
 *
 * @property RefUser $child
 * @property RefUser $parent
 */
class RefUserParent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ref_user_parent';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['child_id', 'parent_id'], 'required'],
            [['child_id', 'parent_id'], 'integer'],
            [['child_id', 'parent_id'], 'unique', 'targetAttribute' => ['child_id', 'parent_id']],
            [['child_id'], 'unique'],
            [['child_id'], 'exist', 'skipOnError' => true, 'targetClass' => RefUser::className(), 'targetAttribute' => ['child_id' => 'id']],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => RefUser::className(), 'targetAttribute' => ['parent_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'child_id' => 'Child ID',
            'parent_id' => 'Parent ID',
        ];
    }

    public function getParentArray()
    {
        $refUser = RefUser::find()->where('id != :id', [':id' => $this->child_id])->all();
        return ArrayHelper::map($refUser, 'id', 'name');

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChild()
    {
        return $this->hasOne(RefUser::className(), ['id' => 'child_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(RefUser::className(), ['id' => 'parent_id']);
    }
}
