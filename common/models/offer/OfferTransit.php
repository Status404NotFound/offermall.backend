<?php

namespace common\models\offer;

use Yii;
use common\models\flow\FlowTransit;

/**
 * This is the model class for table "offer_transit".
 *
 * @property integer $transit_id
 * @property string $url
 * @property integer $offer_id
 *
 * @property FlowTransit[] $flowTransits
 * @property Offer $offer
 */
class OfferTransit extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'offer_transit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_id'], 'integer'],
            [['url', 'name'], 'string', 'max' => 255],
            [['offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Offer::className(), 'targetAttribute' => ['offer_id' => 'offer_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'transit_id' => Yii::t('app', 'Transit ID'),
            'url' => Yii::t('app', 'Url'),
            'offer_id' => Yii::t('app', 'Offer ID'),
        ];
    }


    /**
     * @inheritdoc
     * @return OfferTransitQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OfferTransitQuery(get_called_class());
    }

    /**
     * @param $offer_id
     * @return array|OfferTransit[]
     */
    public static function getOfferTransits($offer_id)
    {
        return self::find()
            ->where(['offer_id' => $offer_id])
            ->asArray()
            ->all();
    }
}
