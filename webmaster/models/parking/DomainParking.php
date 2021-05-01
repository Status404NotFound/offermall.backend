<?php

namespace webmaster\models\parking;

use yii\db\ActiveRecord;

class DomainParking extends ActiveRecord
{
    public const EXIST_DOMAIN_MODE = 0;
    public const OWN_DOMAIN_MODE = 1;

    public static function tableName()
    {
        return '{{wm_domains}}';
    }

    public function rules()
    {
        return [
            [['wm_id', 'name'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Domain ID',
            'name' => 'Domain name',
            'wm_id' => 'Webmaster ID',
            'flow_id' => 'Flow ID',
            'flow_name' => 'Flow Name',
            'mode' => 'Parking mode',
            'landing_id' => 'Landing ID',
            'landing_url' => 'Landing Url',
            'offer_id' => 'Offer ID',
            'offer_name' => 'Offer Name',
            'geo_id' => 'Geo ID',
        ];
    }

    public static function getAllUserDomains($userId)
    {
        return self::find()->where(['wm_id' => $userId])->all();
    }

    public static function getFlowByDomain($domain)
    {
        return self::find()->where(['name' => $domain])->one();
    }
}