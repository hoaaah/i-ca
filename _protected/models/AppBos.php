<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "app_bos".
 *
 * @property string $codename
 * @property string $version
 * @property int $release_number
 * @property string $performed_at
 */
class AppBos extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'app_bos';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['codename', 'version', 'release_number', 'performed_at'], 'required'],
            [['release_number'], 'integer'],
            [['performed_at'], 'safe'],
            [['codename', 'version'], 'string', 'max' => 20],
            [['performed_at'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'codename' => 'Codename',
            'version' => 'Version',
            'release_number' => 'Release Number',
            'performed_at' => 'Performed At',
        ];
    }
}
