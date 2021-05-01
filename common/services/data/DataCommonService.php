<?php

namespace common\services\data;

use common\models\geo\Countries;

class DataCommonService
{


    public function getDeliveryButtons()
    {
        return [
            [
                'delivery_type' => 'fulfillment',
                'credentials' => 'somebody',
                'button_name' => 'UAE'
            ],
            ['delivery_type' => 'fulfillment',
                'credentials' => 'sb_bahrein',
                'button_name' => 'Bahrain'
            ],
            [
                'delivery_type' => 'fulfillment',
                'credentials' => 'ksa',
                'button_name' => 'KSA'
            ],
            [
                'delivery_type' => 'dropship',
                'credentials' => 'UCT_dropship',
                'button_name' => 'UAE Dropship'
            ],
        ];
    }

    public function getDeliveryCountries()
    {
        return Countries::find()->where(['in', 'id', [228, 17, 190]])->asArray()->all();
    }

    public function getAllCountries()
    {
        return Countries::find()->asArray()->all();
    }
}