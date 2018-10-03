<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "auth_assignment".
 *
 * @property string $item_name
 * @property int $user_id
 * @property int $created_at
 *
 * @property AuthItem $itemName
 */
class AGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'A_GROUP';
    }

    public static function getDb() {
        return Yii::$app->dbcs;
    }    

}
