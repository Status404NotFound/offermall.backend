<?php
/**
 * Created by PhpStorm.
 * User: ihor-fish
 * Date: 05.04.18
 * Time: 17:52
 */

namespace common\services\webmaster;


use common\models\landing\Landing;
use common\models\webmaster\parking\ParkingDomain;

class DomainParkingSrv
{
    public $domain_name;
    public $parkingDomain;

    public function __construct(string $domain_name)
    {
        $this->domain_name = $domain_name;
    }

    public function getLanding()
    {
        $parking = $this->getParkingDomain();

        if ($parking){
            $landing = Landing::find()
                ->join('LEFT JOIN', 'flow_landing', 'flow_landing.landing_id = landing.landing_id')
                ->where(['flow_landing.flow_id' => $parking->flow_id])
                ->one();

            return $landing;
        }

        return false;

    }

    public function getParkingDomain()
    {
//        $parkingDomain = ParkingDomain::find()->where(['like', 'domain_name', $this->domain_name])->one();
        if (!isset($this->parkingDomain))
        {
            $parkingDomain = ParkingDomain::find()->where(['domain_name' => $this->domain_name])->andWhere(['is_deleted' => 0])->one();
            $this->parkingDomain = $parkingDomain;
            return $parkingDomain;
        }else{
            return $this->parkingDomain;
        }

    }
}