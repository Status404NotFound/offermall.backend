<?php

use yii\db\Migration;

/**
 * Class m600000_400023_init_service_delivery_stickers
 */
class m160000_400023_init_service_delivery_stickers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('delivery_stickers', 'is_service', 'smallint(1) DEFAULT 0 AFTER is_active');
        $this->insertDeliveryServices();
    }

    public function insertDeliveryServices()
    {
        $services_array =
            [
                ['delivery_name' => 'FETCHR UAE', 'color' => '#e59290'],
                ['delivery_name' => 'FETCHR BAHRAIN', 'color' => '#e5ac90'],
                ['delivery_name' => 'FETCHR KSA', 'color' => '#e5ce90'],
                ['delivery_name' => 'FETCHR JORDAN', 'color' => '#5e7e1a'],
                ['delivery_name' => 'FETCHR EGYPT', 'color' => '#24681e'],
                ['delivery_name' => 'FETCHR OMAN', 'color' => '#31da79'],
                ['delivery_name' => 'FETCHR DROPSHIP', 'color' => '#1d8781'],
                ['delivery_name' => 'MARA UAE', 'color' => '#1c7dce'],
                ['delivery_name' => 'COURIERPLUS KENYA', 'color' => '#766aed'],
                ['delivery_name' => 'MAMBO KENYA', 'color' => '#9a43c2'],
                ['delivery_name' => 'ARAMEX KENYA', 'color' => '#e590bd'],
                ['delivery_name' => 'DTDC KENYA', 'color' => '#e59099'],
                ['delivery_name' => 'ACE NIGERIA', 'color' => '#5c8a8a'],
                ['delivery_name' => 'COURIERPLUS NIGERIA', 'color' => '#adad85'],
                ['delivery_name' => 'RED STAR NIGERIA', 'color' => '#1a53ff'],
                ['delivery_name' => 'MAX NIGERIA', 'color' => '#aa00ff'],
                ['delivery_name' => 'MARA KUWAIT', 'color' => '#ff6600'],
            ];

        foreach ($services_array as $services) {
            $this->insert('delivery_stickers',
                ['sticker_name' => $services['delivery_name'], 'sticker_color' => $services['color'], 'owner_id' => 6,
                    'is_service' => 1, 'created_by' => 1, 'updated_by' => 1]);
        }
    }
}
