<?php

use common\models\delivery\DeliveryApi;
use common\modules\user\models\Permission;
use yii\db\Migration;

/**
 * Class m181008_150457_add_api_shipa_delivery
 */
class m181008_150457_add_api_shipa_delivery extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $delivery_api = new DeliveryApi();
        $delivery_api->api_name = 'ShipaDelivery';
        $delivery_api->api_alias = 'Shipa Delivery';
        $delivery_api->save();
        
        // NOTE Exception: Illegal offset type (/home/evild/crm/backend/common/models/delivery/UserDeliveryApi.php:47)
        
        //$delivery_api = new UserDeliveryApi();
        //$delivery_api->api_name = 'SHIPA DELIVERY';
        //$delivery_api->delivery_api_id = 5;
        //$delivery_api->country_id = 118;
        //$delivery_api->credentials = 'mara_uae';
        //$delivery_api->permission_api_id = 188;
        //$delivery_api->save();
        
        $this->insert('user_delivery_api', [
            'api_name'          => 'SHIPA DELIVERY',
            'delivery_api_id'   => $delivery_api->delivery_api_id,
            'country_id'        => 118,
            'credentials'       => 'mara_uae',
            'permission_api_id' => Permission::shipaDelivery,
        ]);
        
        // NOTE Exception: Calling unknown method: yii\console\Request::post() (/home/evild/crm/backend/vendor/yiisoft/yii2/base/Component.php:300)
        
        //$delivery_stickers = new DeliveryStickers();
        //$delivery_stickers->sticker_name = 'SHIPA DELIVERY';
        //$delivery_stickers->sticker_color = '#cbc0ff';
        //$delivery_stickers->owner_id = 6;
        //$delivery_stickers->is_active = 1;
        //$delivery_stickers->is_service = 1;
        //$delivery_stickers->created_by = 1;
        //$delivery_stickers->save();
        
        $this->insert('delivery_stickers', [
            'sticker_name'  => 'SHIPA DELIVERY',
            'sticker_color' => '#cbc0ff',
            'owner_id'      => 6,
            'is_active'     => 1,
            'is_service'    => 1,
            'created_by'    => 1
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('delivery_stickers', ['sticker_name' => 'SHIPA DELIVERY']);
        $this->delete('user_delivery_api', ['api_name' => 'SHIPA DELIVERY']);
        $this->delete('delivery_api',      ['api_name' => 'ShipaDelivery']);
    }
}
