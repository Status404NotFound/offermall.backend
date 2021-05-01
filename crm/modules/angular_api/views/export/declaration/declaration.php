<?php

use common\models\offer\targets\advert\sku\TargetAdvertSku;
use common\models\offer\targets\advert\sku\TargetAdvertSkuRules;

/* @var $order \common\models\order\Order */
//$currency = \common\models\geo\Currency::find()->select('currency_name')
//    ->where(['currency_id' => $order->targetAdvert->targetAdvertGroup->currency_id])
//    ->one();
//$currency_name = $currency->currency_name;

//\common\helpers\FishHelper::debug($order);
?>


<div class="offer_half_list">
    <div class="column">
        <span>Declaration #: <?= $order->order_hash ?></span>
        <span>Courier: ____________</span><br>
        <span>Create Date: <?= date("Y-m-d") ?></span>
    </div>
    <div class="row2">
        <div>
            <span>Sender: </span>
            <span>Test company</span>
        </div>
        <div>
            <span>Receiver</span>
            <span>Name: <?= $order->customer->name ?></span>
            <span>Delivery date: <?= date("Y-m-d", strtotime($order->delivery_date)) ?></span>
            <span>Phone: <?= $order->customer->phone ?></span>
            <span>Address: <?= $order->customer->address ?></span>
        </div>
    </div>

    <h4><?= $order->offer->offer_name ?></h4>
    <table>
        <tr class="thead">
            <th>Item</th>
            <th>Price of 1 pc, AED</th>
            <th>Quantity of pcs</th>
            <th>Amount</th>
        </tr>
        <?php foreach ($order->orderSku as $orderSku) : ?>
            <tr>
                <td><?= $orderSku->sku->sku_name ?></td>
                <td><?php
                    // TODO: Put getiting into Controller or Service
                    $targetAdvertSku = TargetAdvertSku::findOne([
                        'target_advert_id' => $order->targetAdvert->target_advert_id,
                        'sku_id' => $orderSku->sku_id
                    ]);
                    if (isset($targetAdvertSku->base_cost)) {
                        echo $targetAdvertSku->base_cost;
                    } else {
                        $targetAdvertSkuRules = TargetAdvertSkuRules::findOne([
                            'target_advert_id' => $order->targetAdvert->target_advert_id,
                            'sku_id' => $orderSku->sku_id,
                            'target_advert_sku_id' => $targetAdvertSku->target_advert_sku_id,
                            'amount' => 1
                        ]);
                        echo $targetAdvertSkuRules->cost;
                    }
                    ?></td>
                <td><?= $orderSku->amount ?></td>
                <td><?= $orderSku->cost ?></td>
            </tr>
        <?php endforeach ?>
        <tr class="total">
            <td>Total</td>
            <td></td>
            <td><?= $order->total_amount ?></td>
            <td><?= $order->total_cost ?></td>
        </tr>
    </table>
    <p><b>Terms: of payment:</b> Cash on delivery</p>
    <div class="subsribe">
        <span>Received: _______________________</span>
        <span>Sinature: _______________________</span>
    </div>
</div>
