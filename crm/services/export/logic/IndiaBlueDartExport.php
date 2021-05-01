<?php

namespace crm\services\export\logic;

use common\helpers\FishHelper;
use common\models\order\Order;
use common\models\order\OrderSku;
use common\models\order\OrderStatus;
use common\models\order\OrderView;
use common\services\timezone\TimeZoneSrv;
use Yii;
use crm\services\export\ExportFactory;
use crm\services\export\ExportInterface;
use yii\db\ActiveQuery;

class IndiaBlueDartExport implements ExportInterface
{
    /**
     * @param null $filters
     * @return array
     */
    public function compareData($filters = null): array
    {
        $dataSheet = ExportFactory::getDataSheet();
        $dataSheet['export_name'] = 'India Blue Dart';

        $orders = $this->findOrders($filters);
        $dataSheet['titles'] = $this->getTitles($orders[0]);

        $dataSheet['data'] = $orders;
        return $dataSheet;
    }

    /**
     * @param null $filters
     * @return array|Order[]
     */
    private function findOrders($filters = null)
    {
        $orders = OrderView::find()->select($this->selectFields())
            ->join('JOIN', 'customer_view', 'customer_view.customer_id = order_view.customer_id')
            ->join('LEFT JOIN', 'order_sku', 'order_sku.order_id = order_view.order_id')
            ->join('LEFT JOIN', 'product_sku', 'product_sku.sku_id = order_sku.sku_id');

        if (!empty($filters['order_id_array'])) {
            $orders->andWhere(['IN', 'order_view.order_id', $filters['order_id_array']]);
        } else {
            $orders->where(['order_view.order_status' => OrderStatus::WAITING_DELIVERY])
                ->andWhere(['order_view.deleted' => 0])
                ->andWhere(['IS NOT', 'order_view.delivery_date', null]);
        }

        if (!empty($owner_id = \Yii::$app->user->identity->getOwnerId()))
            $orders->andWhere(['order_view.owner_id' => $owner_id]);

        return $orders->groupBy('order_view.order_id')->asArray()->all();
    }

    /**
     * @return array
     */
    private function selectFields(): array
    {
//        $tz = new TimeZoneSrv();
        $fields = [
            '("MAA") AS OriginArea',
            '("504103") AS CustomerCode',
            '("Trinity Trading Company") AS CustomerName',
            '("98/4, Poonamallee High Road") AS CustomerAddress1',
            '("Vanagaram") AS CustomerAddress2',
            '("CHENNAI") AS CustomerAddress3',
            '("600095") AS CustomerPincode',
            '("7338930761") AS CustomerTelephone',
            '("7338930761") AS CustomerMobile',
            '("trinitytradingindia@gmail.com") AS CustomerEmailID',
            '("Mohammed") AS Sender',
            '("FALSE") AS IsToPayCustomer',
            '("") AS VendorCode',
            '("1") AS CustomerLatitude',
            '("1") AS CustomerLongitude',
            '("1") AS CustomerAddressinfo',
            'customer_view.name AS ConsigneeName',
            'customer_view.address AS ConsigneeAddress1',
            'customer_view.address AS ConsigneeAddress2',
            'customer_view.city_name AS ConsigneeAddress3',
            'customer_view.pin AS ConsigneePincode',
            'customer_view.phone AS ConsigneeTelephone',
            'customer_view.phone AS ConsigneeMobile',
            'customer_view.name AS ConsigneeAttention',

            '("") AS ConsigneeEmailID',
            '("") AS ConsigneeLatitude',
            '("") AS ConsigneeLongitude',
            '("") AS ConsigneeAddressinfo',

            '("") AS ConsigneeCountryCode',
            '("") AS ConsigneeStateCode',
            '("") AS ReturnAddress1',
            '("") AS ReturnAddress2',
            '("") AS ReturnAddress3',
            '("") AS ReturnPincode',
            '("") AS ReturnTelephone',
            '("") AS ReturnMobile',
            '("") AS ReturnEmailID',
            '("") AS ReturnContact',
            '("") AS ManifestNumber',
            '("") AS ReturnLatitude',
            '("") AS ReturnLongitude',
            '("") AS ReturnAddressinfo',
            '("A") AS ProductCode',
            '("NDOX") AS ProductType',
            '("C") AS SubProductCode',

            '("1") AS PieceCount',
            '("0.25") AS ActualWeight',

            '("") AS PackType',
            '("") AS InvoiceNo',

            'GROUP_CONCAT(CONCAT(product_sku.sku_name, \': \', order_sku.amount) ORDER BY order_sku.sku_id SEPARATOR \'; \') AS SpecialInstruction',
            'order_view.total_cost AS DeclaredValue',
            'order_view.total_cost AS CollectableAmount',
            'order_view.order_hash AS CreditReferenceNo',

            '("") AS PickupDate', '("") AS PickupTime', '("") AS CommodityDetail1', '("") AS CommodityDetail2', '("") AS CommodityDetail3', '("") AS Length', '("") AS Breadth', '("") AS Height', '("") AS Count', '("") AS AWBNo', '("") AS RegisterPickup', '("") AS DeliveryTimeSlot', '("") AS IsReversePickup', '("") AS IsForcePickup', '("") AS ParcelShopCode', '("") AS ForwardAWBNo', '("") AS ForwardLogisticCompName', '("") AS CreditReferenceNo2', '("") AS CreditReferenceNo3', '("") AS PickupMode', '("") AS PickupType', '("") AS ItemCount', '("") AS IsPartialPickup', '("") AS TotalCashPaytoCustomer', '("") AS PreferredPickupTimeSlot', '("") AS DeferredDeliveryDays', '("") AS Officecutofftime', '("") AS ItemDetails', '("") AS IsDedicatedDeliveryNetwork', '("") AS CustomerEDD', '("") AS WaybillNumber', '("") AS DestinationArea', '("") AS DestinationLocation', '("") AS ErrorMessage', '("") AS IsError', '("") AS PickupTokenNumber', '("") AS CustomerRequestPUDate', '("") AS ShipmentPickupDate', '("") AS IDColumn',
        ];
        return $fields;
    }

    /**
     * @param $order
     * @return array [
     * 'Order hash',
     * 'Created at',
     * 'Delivery date',
     * 'Name',
     * 'Phone',
     * 'Address',
     * 'Declaration',
     * 'Offer',
     * 'Pcs',
     * 'Sku count',
     * 'Color',
     * 'Size',
     * 'Emirate',
     * 'Caller',
     * 'Time',
     * 'Status'
     * ]
     **/
    private function getTitles($order)
    {
        return explode(',',
            str_replace('_', ' ',
                ucwords(
                    implode(',',
                        array_keys($order)
                    ), ','
                )
            )
        );
    }
}