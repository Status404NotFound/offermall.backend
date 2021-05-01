<?php

namespace common\services\partner;

use common\models\order\Order;
use common\models\PartnerCrm;
use common\models\PartnerOrderToSend;
use common\models\SendedToPartner;
use Yii;
use yii\base\Exception;

/**
 * Class PartnerService
 * @package common\services\partner
 */
class PartnerService
{
    /**
     * @param Order $order
     * @param PartnerCrm $partner
     * @param $geo_iso
     * @param bool $auto
     * @throws \Exception
     */
    public function sendOrderToPartner(Order $order, PartnerCrm $partner, $geo_iso, $auto = false)
    {
        $field = $auto ? 'auto_send_to_' . $partner->slug : 'send_to_' . $partner->slug;
        if ($order->targetAdvert->targetAdvertGroup->$field === 1) {
            try {
                $result = $this->initSend($order, $partner->slug, $geo_iso);
                $this->afterSend($result, $order->order_id, $partner->id);
            } catch (\Exception $e) {
                Yii::error($e->getMessage(), 'partner_e');
                throw $e;
            }
        }
    }

    public function sendToMyLandCrm(Order $order, string $geo_iso)
    {
        if ((int)$order->targetAdvert->advert_id === Yii::$app->params['my_land_crm_offers_config']['advert_id']) {
            try {
                $result = $this->initSend($order, 'my_land_crm', $geo_iso);
                $this->afterSend($result, $order->order_id, Yii::$app->params['my_land_crm_offers_config']['partner_id']);
            } catch (\Exception $e) {
                Yii::error($e->getMessage(), 'my_land_crm_e');
                throw $e;
            }
        }
    }

    /**
     * @param Order $order
     * @param $geo_iso
     * @param bool $auto
     */
    public function sendOrderToPartners(Order $order, $geo_iso, $auto = false)
    {
        foreach (PartnerCrm::find()->all() as $partner) {
            $field = $auto ? 'auto_send_to_' . $partner->slug : 'send_to_' . $partner->slug;
            if ($order->targetAdvert->targetAdvertGroup->$field === 1) {
                try {
                    $result = $this->initSend($order, $partner->slug, $geo_iso);
                    $this->afterSend($result, $order->order_id, $partner->id);
                } catch (\Exception $e) {
                    Yii::error($e->getMessage(), 'partner_e');
                }
            }
        }
    }

    /**
     * @param Order $order
     * @param $slug
     * @param $geo_iso
     * @return mixed
     */
    private function initSend(Order $order, $slug, $geo_iso)
    {
        $partnerService = PartnerFactory::createPartner($slug);
        $partnerService->setGeo($geo_iso);
        return $partnerService->send($order);
    }

    /**
     * @param $result
     * @param $order_id
     * @param $partner_id
     * @throws Exception
     */
    private function afterSend($result, $order_id, $partner_id)
    {
        if (isset($result['status']) && $result['status'] === 'success' && isset($result['order_id'])) {
            $this->saveOrderToSendedTable($order_id, $partner_id, $result['order_id']);
        } else {
            if (isset($result['message'])) {
                $message = is_array($result['message'])
                    ? array_values($result['message'])[0]
                    : (is_string($result['message'])
                        ? $result['message']
                        : 'Something went wrong. ') . json_encode($result);
            } else {
                $message = 'Something went wrong. ' . json_encode($result);
            }
            throw new Exception('Order ID: ' . $order_id . PHP_EOL . $message);
        }
    }

    /**
     * @param Order $order
     * @param string $geo_iso
     * @param bool $auto
     */
    public function savePartnerOrderToSend(Order $order, string $geo_iso, $auto = false): void
    {
        $model = new PartnerOrderToSend;
        $model->order_id = $order->order_id;
        $model->iso = $geo_iso;
        foreach (PartnerCrm::find()->all() as $partner) {
            $field = $auto ? 'auto_send_to_' . $partner->slug : 'send_to_' . $partner->slug;
            if ($order->targetAdvert->targetAdvertGroup->$field === 1
                && (int)$order->targetAdvert->advert_id !== (int)Yii::$app->params['my_land_crm_offers_config']['advert_id']) {
                $model->partner_id = $partner->id;
                try {
                    if (!PartnerOrderToSend::find()->where(['partner_id' => $model->partner_id, 'order_id' => $order->order_id])->one() && !$model->save()) {
                        throw new \Exception(json_encode($model->errors));
                    }
                } catch (\Exception $e) {
                    /** dont throw $e cause of regorder catch =) */
                    Yii::error($e->getMessage(), 'partner_e');
                }
            }
        }

        /** logic only for myland crm partner */
        if ((int)$order->targetAdvert->advert_id === (int)Yii::$app->params['my_land_crm_offers_config']['advert_id']) {
            $model->partner_id = Yii::$app->params['my_land_crm_offers_config']['partner_id'];
            try {
                if (!PartnerOrderToSend::find()->where(['partner_id' => $model->partner_id, 'order_id' => $order->order_id])->one() && !$model->save()) {
                    throw new \Exception(json_encode($model->errors));
                }
            } catch (\Exception $e) {
                Yii::error($e->getMessage(), 'my_land_crm_e');
            }
        }
    }

    /**
     * @param $order_id
     * @param $partner_id
     * @param $remote_order_id
     */
    private function saveOrderToSendedTable($order_id, $partner_id, $remote_order_id)
    {
        $model = new SendedToPartner();
        $model->partner_id = $partner_id;
        $model->order_id = $order_id;
        $model->remote_order_id = $remote_order_id;
        $model->save();
    }
}
