<?php

use common\models\offer\targets\advert\sku\TargetAdvertSku;
use common\models\offer\targets\advert\sku\TargetAdvertSkuRules;

/* @var $order \common\models\order\Order */
/* @var $template \common\models\delivery\UserRequisites */

?>

<div class="container">
    <div class="top">
        <div class="left">
            <?=nl2br($template->description)?>
        </div>
        <div class="right">
            <p class="tax">TAX INVOICE</p>
            <p class="num"># <span class="red"><?= $order->order_hash ?></span></p>
            <p>Balance Due</p>
            <p class="ad"><?= $order->total_cost ?> AED <span class="red"><br><span class="order"></span></span></p>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="bottom">
        <div class="left">
            <p class="no-bold">Bill To</p>
            <p><span class="red"><?= $order->customer->name ?></span></p>
            <p><span class="red"><?= $order->customer->address ?></span></p>
            <p><span class="red"><?= $order->customer->phone ?></span></p>
        </div>
        <div class="right">
            <p>Invoice Date: <span><?= date("Y-m-d") ?></span></p>
            <p>Terms: <span class="red3">Due on Receipt</span></p>
            <p>Due Date: <span></span><?= date("Y-m-d", strtotime($order->delivery_date)) ?></span></p>
        </div>
    </div>
    <div class="clearfix"></div>
    <table class="table1">
        <tr>
            <td>#</td>
            <td>Item & Description</td>
            <td>Qty</td>
            <td>Rate 1 pcs</td>
            <td>Tax (5.00%)</td>
            <td>Amount</td>
        </tr>
        <?php
        $i = 1;
        foreach ($order->orderSku as $orderSku) { ?>
            <?php

            $targetAdvertSku = TargetAdvertSku::findOne([
                'target_advert_id' => $order->targetAdvert->target_advert_id,
                'sku_id' => $orderSku->sku_id
            ]);

            $targetAdvertSkuRules = TargetAdvertSkuRules::findOne([
                'target_advert_id' => $order->targetAdvert->target_advert_id,
                'sku_id' => $orderSku->sku_id,
                'target_advert_sku_id' => $targetAdvertSku->target_advert_sku_id,
                'amount' => 1
            ]);

            if (isset($orderSku->cost)) {
                $tax_rate = round($orderSku->cost - ($orderSku->cost / 1.05), 2);
            }

            if ($orderSku->cost == 0) {
                $tax_rate = '-';
            } else {
                $tax_rate = round($order->total_cost - ($order->total_cost / 1.05), 2);
            }

            ?>
            <tr class="bca">
                <td><?= $i++ ?></td>
                <td><?= $orderSku->sku->sku_alias . '-' . $order->offer->offer_name ?></td>
                <td><?= $orderSku->amount ?></td>
                <td><?php

                    if (!isset($orderSku->cost)) {
                        if (isset($targetAdvertSku->base_cost)) {
                            echo round($targetAdvertSku->base_cost - ($targetAdvertSku->base_cost - ($targetAdvertSku->base_cost / 1.05)), 2);
                        } else {
                            echo round($targetAdvertSkuRules->cost - ($targetAdvertSkuRules->cost - ($targetAdvertSkuRules->cost / 1.05)), 2);
                        }
                    }

                    if ($orderSku->cost == 0) {
                        $data = isset($targetAdvertSkuRules->cost) ? $targetAdvertSkuRules->cost : 0;
                        echo round($data, 2);
                    } else {
                        echo round($orderSku->cost - ($orderSku->cost - ($orderSku->cost / 1.05)), 2);
                    }

                    ?></td>
                <td class="mas">
                    <p><?= $tax_rate ?></p>
                    <!--                    <p class="abs">5.00%</p>-->
                </td>
                <td><?php

                    if (isset($orderSku->cost)) {
                        echo round($orderSku->cost - ($orderSku->cost - ($orderSku->amount * ($orderSku->cost / 1.05))), 2);
                    }

                    if (!isset($orderSku->cost) && $orderSku->cost == 0) {
                        echo round($order->total_cost - ($order->total_cost - ($order->total_cost / 1.05)), 2);
                    }

                    ?></td>
            </tr>
        <?php } ?>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td class="bold">Sub Total</td>
            <td><?= round($order->total_cost - ($order->total_cost / 1.05), 2) ?></td>
            <td><?= round($order->total_cost - ($order->total_cost - ($order->total_cost / 1.05)), 2) ?></td>
        </tr>
        <tr class="bdn">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="bold">Total</td>
            <td class="bold">AED <?= $order->total_cost ?></td>
        </tr>
        <tr class="bdn">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="bold sss">Balance Due</td>
            <td class="bold sss">AED <?= $order->total_cost ?></td>
        </tr>
    </table>
    <table class="table2">
        <caption>Tax Summary</caption>
        <tr>
            <td>Tax Details</td>
            <td>Taxable Amount (AED)</td>
            <td>Tax Amount (AED)</td>
        </tr>
        <tr>
            <td>Standart Rate (5%)</td>
            <td><?= $order->total_cost ?></td>
            <td><?= round($order->total_cost - ($order->total_cost / 1.05), 2) ?></td>
        </tr>
        <tr>
            <td><span class="bold">Total</span></td>
            <td><span class="bold">AED <?= $order->total_cost ?></span></td>
            <td><span class="bold">AED <?= round($order->total_cost - ($order->total_cost / 1.05), 2) ?></span></td>
        </tr>
    </table>
    <p class="first">Notes</p>
    <p class="last">Thanks for your order</p>
    <div style="page-break-before:always"></div>
</div>