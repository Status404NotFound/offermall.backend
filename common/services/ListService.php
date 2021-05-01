<?php

namespace common\services;

use common\models\DataList;
use common\models\offer\targets\advert\TargetAdvert;
use common\modules\user\models\tables\User;
use Yii;
use yii\rbac\Role;

class ListService
{

    public $owner;
    public $offers;

    public function __construct()
    {
        $this->owner = Yii::$app->user->identity->getOwnerId();

        $this->offers = $this->getOffers();

    }

    public function usersByRole()
    {
        new DataList();
    }

    private function getOffers()
    {
        $offers_query = TargetAdvert::find()
            ->select('offer.offer_id, offer.offer_name')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert.target_advert_group_id = target_advert_group.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = advert_offer_target.offer_id');

        if (!is_null($this->owner)) $offers_query ->where(['target_advert.advert_id'=>$this->owner]);

        $offers = $offers_query ->groupBy('offer.offer_id')
            ->asArray()
            ->all();

        return $offers;
    }

}