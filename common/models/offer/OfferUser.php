<?php

namespace common\models\offer;

use Yii;

/**
 * This is the model class for table "offer_user".
 *
 * @property integer $offer_id
 * @property integer $user_id
 * @property boolean $is_active
 */
class OfferUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'offer_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_id', 'user_id',], 'integer'],
            ['is_active', 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'offer_id' => 'Offer ID',
            'user_id' => 'User ID',
            'is_active' => 'Is available for operator',
        ];
    }
}
