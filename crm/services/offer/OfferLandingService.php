<?php

namespace crm\services\offer;


use common\helpers\FishHelper;
use common\models\landing\Landing;
use common\models\landing\LandingGeo;
use common\models\landing\OfferGeoPrice;
use common\models\offer\Offer;
use common\models\offer\OfferGeoThankYouPage;
use common\models\offer\OfferTransit;
use common\models\offer\targets\advert\AdvertOfferTarget;
use yii\base\Exception;
use Yii;
use yii\helpers\ArrayHelper;

class OfferLandingService
{
    public $offer_id;


    public function __construct(int $offer_id)
    {
        if (Offer::findOne($offer_id)) $this->offer_id = $offer_id;
        else throw new Exception('No offer exist!');
    }

    public function getLandings()
    {
        $landings = Landing::find()->where(['offer_id' => $this->offer_id])->andWhere(['wm_id' => 0])->asArray()->all();
//        foreach ($landings as $key=>$landing)
//        {
//            $landings[$key]['geo'] = LandingGeo::find()->where(['landing_id' => $landing['landing_id']])->asArray()->all();
//        }

        return $landings;
    }


    public function getTransits()
    {
        $transits = OfferTransit::find()
            ->where(['offer_id' => $this->offer_id])
            ->asArray()
            ->all();
        return $transits;
    }

    public function getFormList()
    {
        //TODO: build logic with form-landing-offer relations
        $forms = Yii::$app->db->createCommand(
            "select `id` as form_id, concat(`id`, ' - ', `name`)as `name` from gen_form_table where offer_id = " . $this->offer_id
        )->queryAll();

        $forms[] = [
            'form_id' => '29',
            'name' => 'Universal English form',
        ];
        return array_values($forms);
    }

    public function saveLandings(array $landings)
    {
        $landings_old = Landing::find()->where(['offer_id' => $this->offer_id])->asArray()->all();
        $landings_old_indexed = ArrayHelper::map($landings_old, 'landing_id', 'name');
        foreach ($landings as $landing) {
            if (isset($landings_old_indexed[$landing['landing_id']])) {
//                var_dump('isset true');

                $landing_model = Landing::findOne($landing['landing_id']);
                $landing_model->name = $landing['name'];
                $landing_model->url = $landing['url'];
                $landing_model->form_id = $landing['form_id'];
                $landing_model->save();


//                $landing_geos_old = LandingGeo::find()->where(['landing_id' => $landing_model->landing_id])->asArray()->all();
//                $landing_geos_old_indexed = ArrayHelper::map($landing_geos_old, 'landing_geo_id', 'landing_id');

//                foreach ($landing['geo'] as $geo)
//                {
//                    if (isset($landing_geos_old_indexed[$geo['landing_geo_id']]))
//                    {
//
//                        $landing_geo_model = LandingGeo::findOne($geo['landing_geo_id']);
//                        $landing_geo_model->currency_id = $geo['currency_id'];
//                        $landing_geo_model->geo_id = $geo['geo_id'];
//                        $landing_geo_model->discount = strval($geo['discount']);
//                        $landing_geo_model->old_price = strval($geo['old_price']);
//                        $landing_geo_model->new_price = strval($geo['new_price']);
//                        $landing_geo_model->save();
//
//
//                        unset($landing_geos_old_indexed[$geo['landing_geo_id']]);
//                    }
//
//                    if ($geo['landing_geo_id'] == null)
//                    {
//                        $landing_geo_model = new LandingGeo();
//                        unset($geo['landing_geo_id']);
//                        $landing_geo_model->landing_id = $landing_model->landing_id;
//                        $landing_geo_model->currency_id = $geo['currency_id'];
//                        $landing_geo_model->geo_id = $geo['geo_id'];
//                        $landing_geo_model->discount = strval($geo['discount']);
//                        $landing_geo_model->old_price = strval($geo['old_price']);
//                        $landing_geo_model->new_price = strval($geo['new_price']);
//                        $landing_geo_model->save();
//                    }
//                }
//
//                foreach ($landing_geos_old_indexed as $landing_geo_id=>$landing_id){
//                    $landing_geo=LandingGeo::findOne($landing_geo_id);
//                    $landing_geo->delete();
//                }

                unset($landings_old_indexed[$landing['landing_id']]);
            }

            if ($landing['landing_id'] == null) {
//                var_dump('null true');

                $landing_model = new Landing();
                unset($landing['landing_id']);
                $landing_model->name = $landing['name'];
                $landing_model->url = $landing['url'];
                $landing_model->form_id = $landing['form_id'];
                $landing_model->offer_id = $this->offer_id;
                $landing_model->save();

//                foreach ($landing['geo'] as $geo)
//                {
//                    $landing_geo_model = new LandingGeo();
//                    unset($geo['landing_geo_id']);
//                    $landing_geo_model->landing_id = $landing_model->landing_id;
//                    $landing_geo_model->currency_id = $geo['currency_id'];
//                    $landing_geo_model->geo_id = $geo['geo_id'];
//                    $landing_geo_model->discount = strval($geo['discount']);
//                    $landing_geo_model->old_price = strval($geo['old_price']);
//                    $landing_geo_model->new_price = strval($geo['new_price']);
//                    $landing_geo_model->save();
//                }

            }
        }

        foreach ($landings_old_indexed as $landing_id => $landing_name) {
            $landing = Landing::findOne($landing_id);
            $landing->delete();
        }

        return true;
    }


    public function saveTransits(array $transits)
    {
        $transits_old = OfferTransit::find()->where(['offer_id' => $this->offer_id])->asArray()->all();
        $transits_old_indexed = ArrayHelper::map($transits_old, 'transit_id', 'name');
        foreach ($transits as $key => $transit) {
            if ($transit['transit_id'] == null) {
                $transit_model = new OfferTransit();

                unset($transits[$key]['transit_id']);
                $transit_model->setAttributes($transit);
                $transit_model->offer_id = $this->offer_id;

                if ($transit_model->save()) continue;
                else return $transit_model->errors;
            }

            if ($transit_model = OfferTransit::findOne($transit['transit_id'])) {
                unset($transits[$key]['transit_id']);
                $transit_model->setAttributes($transit);
                $transit_model->offer_id = $this->offer_id;

                if ($transit_model->save()) {
                    unset($transits_old_indexed[$transit['transit_id']]);
                    continue;
                } else return $transit_model->errors;
            }

        }

        foreach ($transits_old_indexed as $transit_id => $transit_name) OfferTransit::findOne($transit_id)->delete();

        return true;
    }

    public function getGeo()
    {
        $geo = AdvertOfferTarget::find()
            ->distinct()
            ->select('advert_offer_target.geo_id, geo.geo_name')
            ->join('LEFT JOIN', 'geo', 'geo.geo_id = advert_offer_target.geo_id')
            ->where(['advert_offer_target.offer_id' => $this->offer_id])
            ->asArray()
            ->all();

        return $geo;
    }

    public function saveOfferGeoPrice(array $offer_geo_prices)
    {
        $old_offer_geo_prices = ArrayHelper::map($this->getOfferGeoPrice(), 'offer_geo_price_id', 'geo_id');
        $offer_thank_you_pages = $this->getOfferThankYouPages($this->offer_id);

        foreach ($offer_geo_prices as $offer_geo_price) {
            if (!$offer_geo_price_model = OfferGeoPrice::findOne($offer_geo_price['offer_geo_price_id'])) $offer_geo_price_model = new OfferGeoPrice();

            $offer_geo_price_model->offer_id = $this->offer_id;
            $offer_geo_price_model->new_price = $offer_geo_price['new_price'];
            $offer_geo_price_model->old_price = $offer_geo_price['old_price'];
            $offer_geo_price_model->discount = $offer_geo_price['discount'];
            $offer_geo_price_model->geo_id = $offer_geo_price['geo_id'];
            $offer_geo_price_model->currency_id = $offer_geo_price['currency_id'];

            if (isset($offer_geo_price['th_u_page_url']) && !empty($offer_geo_price['th_u_page_url'])) {
                $offerGeoThankYouPage = OfferGeoThankYouPage::find()
                        ->where([
                            'offer_id' => $this->offer_id,
                            'geo_id' => $offer_geo_price['geo_id']
                        ])->one() ?? new OfferGeoThankYouPage();
                $offerGeoThankYouPage->offer_id = $this->offer_id;
                $offerGeoThankYouPage->geo_id = $offer_geo_price['geo_id'];
                $offerGeoThankYouPage->url = $offer_geo_price['th_u_page_url'];

                if (!$offerGeoThankYouPage->save()) {
                    var_dump($offerGeoThankYouPage->errors);
                    exit;
                }

                if (isset($offer_thank_you_pages[$offer_geo_price['geo_id']]))
                    unset($offer_thank_you_pages[$offer_geo_price['geo_id']]);
            }

            if (!$offer_geo_price_model->save()) {
                var_dump($offer_geo_price_model->errors);
                exit;
            }

            if (isset($old_offer_geo_prices[$offer_geo_price_model->offer_geo_price_id]))
                unset($old_offer_geo_prices[$offer_geo_price_model->offer_geo_price_id]);
        }

        foreach ($old_offer_geo_prices as $offer_geo_price_id => $geo_id) {
            OfferGeoPrice::findOne($offer_geo_price_id)->delete();
        }
        foreach ($offer_thank_you_pages as $offer_geo_thank_you_page) {
            $offer_geo_thank_you_page->delete();
        }

        return true;
    }

    public function getOfferThankYouPages($offer_id)
    {
        $old_offer_geo_thank_you_pages = OfferGeoThankYouPage::find()
            ->where(['offer_id' => $offer_id])->indexBy('geo_id')->all();

        return $old_offer_geo_thank_you_pages;
    }

    public function getOfferGeoPrice()
    {
        $offer_geo_prices = OfferGeoPrice::find()
            ->select([
                'offer_geo_price.*',
                'currency.currency_name'
            ])
            ->join('JOIN', 'currency', 'offer_geo_price.currency_id = currency.currency_id')
            ->where(['offer_geo_price.offer_id' => $this->offer_id])
            ->asArray()->all();

        foreach ($offer_geo_prices as &$offer_geo_price) {
            $offerGeoThankYouPage = OfferGeoThankYouPage::find()
                ->where([
                    'offer_id' => $this->offer_id,
                    'geo_id' => $offer_geo_price['geo_id']
                ])->one();
            $offer_geo_price['th_u_page_url'] = $offerGeoThankYouPage ? $offerGeoThankYouPage->url : null;
        }

        return $offer_geo_prices;
    }

}
