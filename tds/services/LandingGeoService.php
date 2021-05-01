<?php

namespace tds\services;
use common\models\geo\Geo;
use common\models\landing\Landing;
use common\models\landing\LandingGeo;
use common\models\landing\OfferGeoPrice;
use common\services\cache\CacheCommonSrv;
use common\services\GeoService;
use common\services\webmaster\DomainParkingSrv;
use Yii;
use yii\web\NotFoundHttpException;

class LandingGeoService
{

    public $landing;
    public $geo;
    public $offer_geo;
    public $result;
    public $request;

    public function __construct($request, $geo_iso = null)
    {
        $this->request = $request;

        $parking = false;
        $parts = parse_url($this->request->referrer);
        if (isset($parts['host']) && $parkingDomain = (new DomainParkingSrv($parts['host']))->getParkingDomain()){
            if (!is_null($parkingDomain->geo_id)) $parking = $parkingDomain;
        }

        if($parking){
            $this->geo = $parking->geo;
        }
        elseif (isset($geo_iso) && !isset($this->geo)){
            $this->geo = Geo::find()->where(['iso' => $geo_iso])->one();
        }
        else{
            $this->geo = new GeoService($request->userIP);
        }

        $data = $this->getData();
        $this->result = $data;

    }

    protected function getData()
    {
        $this->landing = $this->findLanding($this->request->get('landing_id'));

        $offer_geo = OfferGeoPrice::find()
            ->select([
                'offer_geo_price.*',
                'offer.offer_hash',
            ])
            ->join('LEFT JOIN', 'offer', 'offer_geo_price.offer_id=offer.offer_id')
            ->where(['offer_geo_price.offer_id' => $this->landing->offer_id])
            ->andWhere(['offer_geo_price.geo_id' => $this->geo->geo_id])
            ->one();

        if (!$offer_geo) $offer_geo = OfferGeoPrice::find()
            ->select([
                'offer_geo_price.*',
                'offer.offer_hash',
            ])
            ->join('LEFT JOIN', 'offer', 'offer_geo_price.offer_id=offer.offer_id')
            ->where(['offer_geo_price.offer_id' => $this->landing->offer_id])
            ->one();

        $this->offer_geo = $offer_geo;

        return [
            'phone_code' => $this->geo->phone_code,
            'currency' => $this->offer_geo->currency->currency_name,
            'old_price' => $this->offer_geo->old_price,
            'new_price' => $this->offer_geo->new_price,
            'discount' => $this->offer_geo->discount,
            'form_id' => $this->landing->form_id,
            'phone_num_count' => $this->geo->phone_num_count,
            'offer_hash' => $this->offer_geo->offer_hash,
        ];
    }

    protected function findLanding($id)
    {
        if ($landing = Landing::findOne($id)) return $landing;

        throw new NotFoundHttpException('Landing not found!');
    }
}