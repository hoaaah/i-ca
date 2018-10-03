<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "ta_cs_progress".
 *
 * @property string $id
 * @property string $cs_id
 * @property string $ta_cs_id
 * @property int $tahapan
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 * @property string $catatan_reviu
 * @property string $time
 *
 * @property TaCs $taCs
 */
class TaCsProgress extends \yii\db\ActiveRecord
{
    const TAHAP_TIM = 1;
    const TAHAP_P3A = 2;
    const TAHAP_KEU = 3;
    const TAHAP_PPK = 4;
    const TAHAP_KPA = 5;
    const TAHAP_SPD = 6;
    const TAHAP_UM = 7;

    /**
     * List of names for each status.
     * @var array
     */
    public static function tahapList(){
        return [
            self::TAHAP_TIM  => 'Tim',
            self::TAHAP_P3A => 'P3A',
            self::TAHAP_KEU => 'Subbag Keuangan',
            self::TAHAP_PPK => 'PPK',
            self::TAHAP_KPA => 'KPA',
            self::TAHAP_SPD => 'Admin Keuangan/SPD',
            self::TAHAP_UM => 'Ketersediaan UM',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ta_cs_progress';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // [['id'], 'required'],
            [['id'], 'thamtech\uuid\validators\UuidValidator'],
            [['id', 'ta_cs_id', 'catatan_reviu'], 'string'],
            [['cs_id', 'tahapan', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['cs_id', 'tahapan', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['time'], 'safe'],
            [['cs_id', 'ta_cs_id', 'tahapan'], 'unique', 'targetAttribute' => ['cs_id', 'ta_cs_id', 'tahapan']],
            [['id'], 'unique'],
            [['ta_cs_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaCs::className(), 'targetAttribute' => ['ta_cs_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cs_id' => 'Cs ID',
            'ta_cs_id' => 'Ta Cs ID',
            'tahapan' => 'Tahapan',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'catatan_reviu' => 'Catatan Reviu',
            'time' => 'Time',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class' => BlameableBehavior::className(),
            ],            
        ];
    }    

    public function beforeSave($insert)
    {   
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // ...custom code here...
        if($this->isNewRecord){
            $this->id = \thamtech\uuid\helpers\UuidHelper::uuid();
        }

        return true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTaCs()
    {
        return $this->hasOne(TaCs::className(), ['id' => 'ta_cs_id']);
    }
}
