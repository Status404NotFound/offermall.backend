<?php

namespace common\services\offer;

use common\helpers\FishHelper;
use common\models\offer\OfferProduct;
use common\models\offer\OfferSku;
use common\models\offer\targets\wm\TargetWmView;
use common\modules\user\models\tables\User;
use common\services\offer\exceptions\OfferServiceException;
use common\services\offer\logic\OfferSave;
use common\models\offer\Offer;
use common\services\offer\logic\OfferSkuSave;
use common\services\ValidateException;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;

class OfferCommonService
{
    public function saveOffer(Offer $offer, $request)
    {
        if ($offer->isNewRecord && $this->issetOfferName($request['offer_name']))
            throw new Exception('Offer name ' . $request['offer_name'] . ' is busy!');

        $tx = Yii::$app->db->beginTransaction();
        try {
            $offer->setAttributes([
                'offer_name' => $request['offer_name'],
                'offer_status' => $request['offer_status'],
                'description' => isset($request['description']) ? $request['description'] : '',
                'offer_hash' => md5($request['offer_name']),
                'img' => $request['img'],
            ]);

            if (!$offer->save())
                FishHelper::debug($offer->errors);
//            throw new ValidateException($offer->errors);

            if (!$this->saveOfferProducts($request['product_id'], $offer->offer_id))
                throw new OfferServiceException();
            $tx->commit();
        } catch (ValidateException $e) {
            $tx->rollBack();
            FishHelper::debug($e->getMessages(), 1, 1);
        } catch (Exception $e) {
            $tx->rollBack();
            FishHelper::debug($e->getMessage(), 1, 1);
        }

//        FishHelper::debug($offer->errors, 1, 1);

        return true;
    }

    public function saveOfferProducts($product_id_array, $offer_id)
    {
        $offerProducts = OfferProduct::find()->where(['offer_id' => $offer_id])->indexBy('product_id')->all();
        foreach ($product_id_array as $product_id) {
            $offerProduct = new OfferProduct();
            $offerProduct->offer_id = $offer_id;
            $offerProduct->product_id = $product_id;
            if (!$offerProduct->save())
                throw new ValidateException($offerProduct->errors);
            unset($offerProducts['product_id']);
        }
        foreach ($offerProducts as $product) {
            if (!$product->delete())
                throw new ValidateException($product->errors);
        }
        return true;
    }

    public function findOffer($offer_id)
    {
        $offer = Offer::find()->where(['offer.offer_id' => $offer_id]);

        $offer->with([
            'advertOfferTargets' => function ($query) {
                if (!empty($owner_id = \Yii::$app->user->identity->getOwnerId())) {
                    $query->join('RIGHT JOIN', 'target_advert_group TAG', 'TAG.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
                        ->join('RIGHT JOIN', 'target_advert TA', 'TA.target_advert_group_id = TAG.target_advert_group_id')
                        ->andWhere(['TA.advert_id' => $owner_id]);
                }
                $query->select('geo.geo_name, geo.iso, order_status.status_name as advert_offer_target_status_name, advert_offer_target.*')
                    ->join('JOIN', 'geo', 'geo.geo_id = advert_offer_target.geo_id')
                    ->join('JOIN', 'order_status', 'order_status.status_id = advert_offer_target.advert_offer_target_status')
                    ->with([
                        'targetAdvertGroups' => function ($query) {
                            if (!empty($owner_id = \Yii::$app->user->identity->getOwnerId())) {
                                $query->join('RIGHT JOIN', 'target_advert TA', 'TA.target_advert_group_id = target_advert_group.target_advert_group_id')
                                    ->andWhere(['TA.advert_id' => $owner_id]);
                            }
                            $query->select('currency.currency_name as currency_name, target_advert_group.*')
                                ->join('JOIN', 'currency', 'currency.currency_id = target_advert_group.currency_id')
                                ->with([
                                    'targetAdvertGroupRules',
                                    'targetAdverts' => function ($query) {
                                        $query->select('user.username as advert_name, target_advert.*')
                                            ->join('JOIN', 'user', 'target_advert.advert_id = user.id')
                                            ->with([
                                                'targetAdvertSku' => function (ActiveQuery $query) {
                                                    $query->select('product_sku.sku_name, product_sku.common_sku_name, target_advert_sku.*')
                                                        ->join('LEFT JOIN', 'product_sku', 'product_sku.sku_id = target_advert_sku.sku_id')
                                                        ->with('targetAdvertSkuRules');
                                                },]);
                                    }]);
                        },
                        'wmOfferTargets' => function ($query) {
                            $query->select('geo.geo_name, order_status.status_name as advert_offer_target_status_name, OS.status_name as wm_offer_target_status_name, wm_offer_target.*')
                                ->join('JOIN', 'geo', 'geo.geo_id = wm_offer_target.geo_id')
                                ->join('JOIN', 'order_status', 'order_status.status_id = wm_offer_target.advert_offer_target_status')
                                ->join('JOIN', 'order_status OS', 'OS.status_id = wm_offer_target.wm_offer_target_status')
                                ->with(['targetWmGroups' => function ($query) {
                                    $query->with(['targetWmGroupRules', 'targetWms' => function ($query) {
                                        $query->select('user.username as wm_name, target_wm.*')
                                            ->join('JOIN', 'user', 'target_wm.wm_id = user.id');
                                    }]);
                                },]);
                        },
                    ]);
            },
//            'offerProducts' => function ($query) {
//                $query->with(['product']);
//            }
        ]);
        return $offer->asArray()->one();
    }

    public function issetOfferName($offer_name)
    {
        return (Offer::find()->where(['offer_name' => $offer_name])->one());
    }

    public function saveSku(OfferSku $offer_sku)
    {
        return Yii::createObject(['class' => OfferSkuSave::class, 'offer_sku' => $offer_sku])->execute();
    }

    public function updateOffer(Offer $offer)
    {
        $offer->save();
        return true;
    }

    /**
     * @param $request
     * @return bool|null
     */
    public function sendWebmasterNotify($request)
    {
        $webmasters = null;

        if ($request['offer_status'] == Offer::STATUS_ON_PAUSE || $request['offer_status'] == Offer::STATUS_ARCHIVED) {
            $statuses = [Offer::STATUS_ON_PAUSE, Offer::STATUS_ARCHIVED];

            $webmasters = TargetWmView::find()
                ->select([
                    'U.username',
                    'U.email',
                    'OV.offer_name',
                    'OV.offer_status'
                ])
                ->from('target_wm_view TWV, user U, offer_view OV')
                ->andWhere('TWV.wm_id = U.id')
                ->andWhere('TWV.offer_id = OV.offer_id')
                ->andWhere(['U.blocked_at' => null])
                ->andWhere(['TWV.offer_id' => $request['offer_id']])
                ->andWhere(['in', 'OV.offer_status', $statuses])
                ->groupBy('U.id')
                ->asArray()
                ->all();

            foreach ($webmasters as $key => $webmaster) {
                $webmaster['offer_status'] = Offer::statusLabel()[$webmaster['offer_status']];

                Yii::$app->mailer->compose('notify_webmaster', ['data' => $webmaster])
                    ->setFrom([Yii::$app->params['smtpEmail'] => 'Crmka'])
                    ->setTo($webmaster['email'])
                    ->setSubject('Offer ' . $webmaster['offer_name'] . ' changed the status')
                    ->send();
            }
        }

        if (!is_null($webmasters)) return true;
        else return false;
    }
}
