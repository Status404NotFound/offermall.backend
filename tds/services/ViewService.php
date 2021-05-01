<?php

namespace tds\services;

use common\models\flow\Flow;
use common\models\landing\Landing;
use common\models\LandingViews;
use common\models\webmaster\parking\ParkingDomain;
use common\services\GeoService;
use common\services\webmaster\DomainParkingSrv;

class ViewService
{
    private $referrer;
    private $landing;
    private $flow;
    private $isUnique;
    private $geo_id;
    private $parking;
    /**
     * ViewService constructor.
     * @param $request
     */
    public function __construct($request)
    {
//        $this->referrer = parse_url($request->post()['referrer']);
        $this->referrer = $this->normalizeUrlParams($request->post());
        $this->isUnique = $request->post()['unique'];
        $this->parking = $this->getParking();
        $this->geo_id = $this->getGeoId($request->userIP);
        $this->landing = $this->getLanding($request);
        $this->flow = $this->getFlow();
        $this->addView();
    }

    /**
     * @param $params
     * @return array|mixed
     */
    public function normalizeUrlParams($params)
    {
        $normalizer = ['subid1', 'subid2', 'subid3', 'subid4', 'subid5'];

        $parts = null;
        foreach ($normalizer as $sub_id) {
            if ($sub_id != null && array_key_exists($sub_id, $params)) {
                $index = str_replace('subid', 'sub_id_', $sub_id);
                $parts .= '&' . $index . '=' . $params[$sub_id];
            }
        }

        $url = isset($params['referrer']) ? parse_url($params['referrer'] . $parts) : [];

        return $url;
    }

    /**
     * @return array|bool
     */
    public function addView()
    {
        $subids = $this->parseUrlParams($this->referrer);

        $landing_views_query = LandingViews::find()
            ->where(['offer_id' => $this->getOfferId()])
            ->andWhere((['geo_id' => $this->geo_id]));

        if (isset($subids['sub_id_1'])) $landing_views_query->andWhere(['sub_id_1' => $subids['sub_id_1']]);
        else $landing_views_query->andWhere(['sub_id_1' => null]);

        if (isset($subids['sub_id_2'])) $landing_views_query->andWhere(['sub_id_2' => $subids['sub_id_2']]);
        else $landing_views_query->andWhere(['sub_id_2' => null]);

        if (isset($subids['sub_id_3'])) $landing_views_query->andWhere(['sub_id_3' => $subids['sub_id_3']]);
        else $landing_views_query->andWhere(['sub_id_3' => null]);

        if (isset($subids['sub_id_4'])) $landing_views_query->andWhere(['sub_id_4' => $subids['sub_id_4']]);
        else $landing_views_query->andWhere(['sub_id_4' => null]);

        if (isset($subids['sub_id_5'])) $landing_views_query->andWhere(['sub_id_5' => $subids['sub_id_5']]);
        else $landing_views_query->andWhere(['sub_id_5' => null]);

        $landing_views_query->andWhere(['date' => $this->getTime()]);
        $landing_views_query->andWhere(['landing_id' => $this->landing->landing_id]);

        if ($this->flow) $landing_views_query->andWhere(['flow_id' => $this->flow->flow_id]);
        else $landing_views_query->andWhere(['flow_id' => null]);

        $landing_views = $landing_views_query->one();

        if ($landing_views) {
            $landing_views->views += 1;
            ($this->isUnique == 'true') ? $landing_views->uniques += 1 : $landing_views->uniques = 1;
//            if ($this->isUnique == 'false') $landing_views->uniques = 1;
//            $landing_views->update();
        } else {
            $landing_views = new LandingViews();
            $landing_views->views += 1;
            ($this->isUnique == 'true') ? $landing_views->uniques += 1 : $landing_views->uniques = 1;
//            $landing_views->uniques += 1;
            $landing_views->flow_id = isset($this->flow) ? $this->flow->flow_id : null;
            $landing_views->offer_id = $this->getOfferId();
            $landing_views->landing_id = $this->landing->landing_id;
            $landing_views->date = $this->getTime();
            $landing_views->geo_id = $this->geo_id;

            if (isset($subids['sub_id_1'])) $landing_views->sub_id_1 = $subids['sub_id_1'];
            if (isset($subids['sub_id_2'])) $landing_views->sub_id_2 = $subids['sub_id_2'];
            if (isset($subids['sub_id_3'])) $landing_views->sub_id_3 = $subids['sub_id_3'];
            if (isset($subids['sub_id_4'])) $landing_views->sub_id_4 = $subids['sub_id_4'];
            if (isset($subids['sub_id_5'])) $landing_views->sub_id_5 = $subids['sub_id_5'];

            //if ($this->isUnique == 'false') $landing_views->uniques = 1;

        }

        return $landing_views->validate() ? $landing_views->save() : $landing_views->errors;
    }

    /**
     * @param $parts
     * @return string
     */
    private function parseReferrerFlowKey($parts)
    {
        $flow_key = isset($parts['path']) && $parts['path'] != '/' ? $parts['path'] : null;
        return trim(str_replace('/', '', $flow_key));
    }

    /**
     * @param $parts
     * @return array|mixed
     */
    private function parseUrlParams($parts)
    {
        if (!empty($parts['query'])) {
            $parts = str_replace('subid', 'sub_id_', $parts['query']);
            return array_reduce(explode('&', $parts), function ($carry, $pairs) {
                $pairs = explode('=', $pairs);
                $carry[$pairs[0]] = $pairs[1];
                return $carry;
            }, []);
        }

        return [];

    }

    private function getParking()
    {
        return new DomainParkingSrv($this->referrer['host']);
    }

    private function getFlow()
    {
        if (!$this->parking->getParkingDomain()) $flow = Flow::find()->where(['flow_key' => $this->parseReferrerFlowKey($this->referrer)])->one();
        else $flow = $this->parking->getParkingDomain()->flow;

        return $flow;
    }

    private function getLanding($request)
    {
        if (!$landing = $this->parking->getLanding()) $landing = Landing::find()->where(['landing_id' => $request->post()['landing_id']])->one();
        return $landing;
    }

    /**
     * @param $ip
     * @return int|mixed
     */
    private function getGeoId($ip)
    {
        $geoService = new GeoService($ip);
        return $geoService->getCountryId();
    }

    /**
     * @return string
     */
    private function getTime()
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date->setTime($date->format("H"), 0, 0);
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * @return int|mixed|null
     */
    private function getOfferId()
    {
        if ($this->flow) return $this->flow->offer_id;
        if ($this->landing) return $this->landing->offer_id;
        return null;
    }
}
