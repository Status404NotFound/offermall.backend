<?php

namespace common\services;

use Yii;
use common\models\geo\Geo;
use common\models\geo\Countries;
use GeoIp2\Database\Reader;
use \GeoIp2\Exception\AddressNotFoundException;

class GeoService
{
    private $location;
    public $userIP;
    public $phone_code;
    public $geo_id;

    public function __construct($userIP)
    {
        $this->userIP = $userIP;
        $this->findLocation();
        $this->geo_id = $this->getCountryId();
        $this->phone_code = $this->getCountryPhoneCode();
        $this->phone_num_count = $this->getPhoneNumCount();
    }

    protected function findLocation()
    {
        $db = Yii::getAlias('@tds/web/GeoIP2-City.mmdb');
        $reader = new Reader($db);
        if ($this->userIP == '127.0.0.1') $this->userIP = '2.51.158.207';
        if ($this->userIP != null) {
            try {
                $this->location = $reader->city($this->userIP);
            } catch (AddressNotFoundException $e) {
                $this->location = false;
            }
        } else {
            $this->location = false;
        }
    }

    public function getCountry()
    {
        if ($this->location === null) $this->findLocation();
        if ($this->location === false) return 'Unknown';
        return $this->location->country->name;
    }

    public function getPhoneNumCount(){
        if (isset($this->geo_id))
        {
            $country = Geo::find()
                ->where(['geo_id' => $this->geo_id])
                ->asArray()
                ->one();
            if (isset($country)) return $country['phone_num_count'];
        }

        return '';
    }

//    public function getCountryId()
//    {
//        $this->getCountry();
//        $country = Countries::find()->select('countries.`id`')->where(['like', 'country_name', $this->location->country->name])->one();
//        if (isset($country)) return $country->id;
//        // TODO: решить эту срань
//        return 228;
//    }

    public function getExtendedCountriesId()
    {
        if ($this->location->country->name === 'Hashemite Kingdom of Jordan') {
            $this->location->country->name = 'Jordan';
        }

        $country = Countries::find()->select('countries.`id`')->where(['like', 'country_name', $this->location->country->name])->one();
        if (isset($country)) return $country->id;
        return 228;
    }

    public function getCountryId()
    {
        $country = Geo::find()->select('geo.`geo_id`')->where(['like', 'geo_name', $this->location->country->name])->one();
        if (isset($country)) return $country->geo_id;
        return $this->getExtendedCountriesId();
    }

    public function getCountryIdByCountryISO($country_iso)
    {
        $country = Geo::find()->where(['iso' => $country_iso])->one();
        if (!empty($country)) return $country->geo_id;
        return null;
    }

    public function getCountryPhoneCode()
    {
        if (isset($this->geo_id))
        {
            $country = Geo::find()
                ->where(['geo_id' => $this->geo_id])
                ->asArray()
                ->one();
            if (isset($country)) return $country['phone_code'];
        }

        return '';

//        $country = Countries::find()
//            ->select('countries.`id`, geo.phone_code')
//            ->join('LEFT JOIN', 'geo', 'geo.geo_id=countries.id')
//            ->where(['like', 'country_name', $this->location->country->name])
//            ->asArray()
//            ->one();
//        if (isset($country)) return $country['phone_code'];
//        return 111;
    }
}
