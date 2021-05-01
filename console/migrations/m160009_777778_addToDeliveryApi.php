<?php

use yii\db\Migration;

/**
 * Class m600009_777778_addToDeliveryApi
 */
class m160009_777778_addToDeliveryApi extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('delivery_api', [
            'api_name' => 'CourierPlus',
            'api_alias' => 'Courier Plus',
        ]);

        $this->insert('user_delivery_api', [
            'api_name' => 'Somebody Courier Plus',
            'delivery_api_id' => 4,
            'advert_id' => 6,
            'advert_name' => 'Somebody',
            'country_id' => 113,
            'credentials' => 'Somebody',
        ]);

        $this->insert('user_delivery_api', [
            'api_name' => 'Somebody Fulfillment EG',
            'delivery_api_id' => 1,
            'advert_id' => 6,
            'advert_name' => 'Somebody',
            'country_id' => 199,
            'credentials' => 'sb_egypt',
        ]);
    }
}
