<?php
namespace webmaster\models\partners;

use Yii;
use yii\db\ActiveRecord;

class PartnerOffers extends ActiveRecord
{
    const OFFER_ONAIR_STATUS = 1; //в воздухе
    const OFFER_ARHIVED_STATUS = 10; //архивирован
    const OFFER_ONPAUSE_STATUS = 0; //на паузе

    public static function tableName()
    {
        return '{{partner_offers}}';
    }

    public function rules()
    {
        return [
            [['advert_id', 'offer_id', 'offer_obj_to_send'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'advert_id' => 'Advert ID',
            'offer_id' => 'Offer ID',
            'offer_name' => 'Offer Name',
            'offer_obj_to_send' => 'Offer Object',
            'offer_status' => 'Offer Status',
            'partner_offer_hash' => 'Offer Hash in Partner CRM'
        ];
    }

    public function getAllOffers() :array
    {
        return self::find()->where(['advert_id' => 268])->all();
    }

    public function getOnAirOffers() :array
    {
        return self::find()->where(['advert_id' => 268])->andWhere(['offer_status' => self::OFFER_ONAIR_STATUS])->all();
    }

    public function getOnPauseOffers() :array
    {
        return self::find()->where(['advert_id' => 268])->andWhere(['offer_status' => self::OFFER_ONPAUSE_STATUS])->all();
    }

    public function getArhivedStatus() :array
    {
        return self::find()->where(['advert_id' => 268])->andWhere(['offer_status' => self::OFFER_ARHIVED_STATUS])->all();
    }

    public function getByOfferId($offerId) :object
    {
        return self::find()->where(['advert_id' => 268])->andWhere(['offer_id' => $offerId])->one();
    }
}