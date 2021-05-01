<?php

namespace common\models\callcenter;

use Yii;

/**
 * This is the model class for table "operator_offer".
 *
 * @property integer $id
 * @property integer $offer_id
 * @property integer $user_id
 * @property integer $is_active
 */
class OperatorOffer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'operator_offer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_id', 'user_id'], 'required'],
            [['offer_id', 'user_id', 'is_active'], 'integer'],
            [['offer_id', 'user_id'], 'unique', 'targetAttribute' => ['offer_id', 'user_id'], 'message' => 'The combination of Offer ID and User ID has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'is_active' => Yii::t('app', 'Is Active'),
        ];
    }
}
