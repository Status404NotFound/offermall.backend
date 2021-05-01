<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_geo".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $country_id
 * @property integer $is_active
 */
class UserGeo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_geo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'country_id', 'is_active'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'country_id' => 'Country ID',
            'is_active' => 'Is Active',
        ];
    }
}
