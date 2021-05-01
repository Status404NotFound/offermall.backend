<?php
namespace common\services\callcenter;

use callcenter\components\operator_config\OperatorConfig;
use common\models\callcenter\AsteriskQueueMember;
use common\models\callcenter\CallQueue;
use common\models\callcenter\OperatorConf;
use common\models\callcenter\OperatorOffer;
use common\models\callcenter\OperatorQueue;
use common\models\callcenter\PhoneLinesSearch;
use common\models\callcenter\OperatorLanguage;
use common\models\DataList;
use common\models\UserGeo;
use common\models\offer\OfferUser;
use common\services\ListService;
use yii\helpers\ArrayHelper;
use common\models\Language;
use Yii;
use yii\web\User;


class OperatorSettingsSrv
{

    /**
     * calling modes constants
     */
    const MANUAL_MODE = 0;
    const AUTO_MODE = 1;

//    const STATUS_OFFLINE = 0;
    const STATUS_ONLINE = 1;
    const STATUS_CALLING = 2;

    const STATUS_ONPAUSE = 3;
    const STATUS_OFFLINE = 4;


    public function getSettings($filters = [])
    {
        $owner_id = \Yii::$app->user->identity->getOwnerId();

        $query = OperatorConf::find()
            ->select(['operator_conf.*',
                      'user.username as operator_name',
                      'user_child.parent as advert_id',
                      'parent_user.username as advert_name'])
            ->join('LEFT JOIN', 'user', 'user.id = operator_conf.operator_id')
            ->join('LEFT JOIN', 'user_child', 'user_child.child = operator_conf.operator_id')
            ->join('LEFT JOIN', 'user as parent_user', 'parent_user.id = user_child.parent');

        if ( !is_null($owner_id)) {
            $query->andWhere(['user_child.parent' => $owner_id]);
        }

        if (isset($filters['filters']['operator'])) $query->andWhere(['operator_conf.operator_id' => $filters['filters']['operator']]);
        if (isset($filters['filters']['call_mode'])) $query->andWhere(['operator_conf.call_mode' => $filters['filters']['call_mode']]);
        if (isset($filters['filters']['status'])) $query->andWhere(['operator_conf.status' => $filters['filters']['status']]);
        if (isset($filters['filters']['sip'])) $query->andWhere(['operator_conf.sip' => $filters['filters']['sip']]);
        if (isset($filters['filters']['channel'])) $query->andWhere(['operator_conf.channel' => $filters['filters']['channel']]);

        $rows = $query->asArray()->all();
        $filter_offers = $filters['filters']['offers'] ?? null;

        foreach ($rows as $index=>$row){
            $rows[$index]['offers'] = $this->getOperatorOffers($row['operator_id']);
            $rows[$index]['queues'] = $this->getOperatorQueues($row['operator_id']);
            $rows[$index]['status'] = $this->getOperatorStatus($row['status']);
            $rows[$index]['languages'] = $this->getOperatorLanguages($row['operator_id']);
            //$userService = new ListService($row['operator_id']);
            //$rows[$index]['offers'] = $userService->getOffers();
        }

        return $rows;
    }

    public function getSetting($operator_id)
    {
        $setting = OperatorConf::find()
            ->select('operator_conf.*, user.username as operator_name')
            ->join('LEFT JOIN', 'user', 'user.id = operator_conf.operator_id')
            ->where(['operator_id' => $operator_id])
            ->asArray()
            ->one();

        $setting['offers'] = $this->getOperatorOffers($operator_id);
        $setting['languages'] = $this->getOperatorLanguages($operator_id);

        return $setting;
    }



    /**
     * function getOperators() return ids of operators that are accessible for current logged user
     * @return array
     */
    public static function getOperators(){
        $operators = (new DataList())->getUsers(\common\modules\user\models\tables\User::ROLE_OPERATOR);
        return ArrayHelper::map($operators, 'user_name', 'user_id');
    }


    public function getOperatorStatus($status = null){

        $statuses = array(
            self::STATUS_OFFLINE => 'Offline',
            self::STATUS_ONLINE => 'On air',
            self::STATUS_CALLING => 'Calling',
            self::STATUS_ONPAUSE => 'On pause',
        );

        if ($status !== null){
            return $statuses[$status];
        }else{
            return $statuses;
        }
    }

    public static function getLanguages()
    {
        $language = Language::find()->select('language_id, name as language_name')->asArray()->all();
        return $language;
    }


    public function getOperatorLanguages($operator_id)
    {
        return OperatorLanguage::find()
            ->select('language.language_id, language.name as language_name')
            ->join('LEFT JOIN', 'language', 'language.language_id = operator_language.language_id')
            ->where(['operator_language.user_id' => $operator_id])
            ->asArray()
            ->all();
    }

    public function getOperatorOffers($operator_id, $filter_offers = null)
    {
        $offers = OperatorOffer::find()
            ->select('offer.offer_id, offer.offer_name')
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = operator_offer.offer_id')
            ->where(['operator_offer.user_id' => $operator_id])
            ->asArray()->all();
        return $offers;
    }

    public function getOperatorQueues($operator_id):array
    {
        $queues = OperatorQueue::find()
            ->where(['operator_id' => $operator_id])
            ->asArray()->all();

        return ArrayHelper::getColumn($queues, 'call_queue_id');
    }

    public function saveOperatorQueues($operator_id, $queues)
    {
        if (is_string($queues)) $queues = [];
        $operator_queues_old = OperatorQueue::find()->where(['operator_id' => $operator_id])->indexBy('call_queue_id')->all();
        $operator_queues_old_indexed = ArrayHelper::index($operator_queues_old, 'call_queue_id');

        foreach($queues as $queue)
        {
            if (!isset($operator_queues_old_indexed[$queue])) $this->addOperatorToQueue($operator_id, $queue);
            else unset($operator_queues_old_indexed[$queue]);
        }

        foreach ($operator_queues_old_indexed as $operator_queue_old) $this->excludeOperatorFromQueue($operator_queue_old);
    }

    private function addOperatorToQueue($operator_id, $call_queue_id)
    {
        $operator_queue = new OperatorQueue();
        $operator_queue->operator_id = $operator_id;
        $operator_queue->call_queue_id = $call_queue_id;
        if($operator_queue->save())
        {
            $callQueue = CallQueue::findOne($call_queue_id);
            $operatorSettings = $this->getSetting($operator_id);
            $asterisk_queue_member = new AsteriskQueueMember();
            $asterisk_queue_member->membername = $operatorSettings['sip'];
            $asterisk_queue_member->queue_name = $callQueue->queue_asterisk_code;
            $asterisk_queue_member->interface = "SIP/" . $operatorSettings['sip'];
            $asterisk_queue_member->save();
        }

        return true;
    }

    private function excludeOperatorFromQueue($operatorQueue)
    {
        $operatorSettings = $this->getSetting($operatorQueue->operator_id);
        $asteriskQueueMembers = AsteriskQueueMember::find()
            ->where(['membername'=>$operatorSettings['sip']])
            ->andWhere(['queue_name' => $operatorQueue->callQueue->queue_asterisk_code])
            ->all();
        foreach ($asteriskQueueMembers as $member) $member->delete();
        $operatorQueue->delete();
        return true;
    }


    public function saveOperatorLanguages($operator_id, $languages){
        if (is_string($languages)) $languages = [];

        OperatorLanguage::deleteAll(['user_id' => $operator_id]);

        foreach ($languages as $language){
            $operator_language = new OperatorLanguage();
            $operator_language->user_id = $operator_id;
            $operator_language->language_id = intval($language);
            $operator_language->is_active = true;
            $operator_language->save();
        }

        return true;
    }


    public function saveOperatorOffers($operator_id, $offers){

        if (is_string($offers)) $offers = [];

        OperatorOffer::deleteAll(['user_id' => $operator_id]);

        foreach ($offers as $offer){
            $operatorOffer = new OperatorOffer();
            $operatorOffer->user_id = $operator_id;
            $operatorOffer->offer_id = intval($offer);
            $operatorOffer->is_active = true;
            $operatorOffer->save();
        }

        return true;
    }

    public static function getOperatorCountries($operator_id, $available = false, $cc_api = false){

        $geo = UserGeo::find()
            ->select(['operator_geo.country_id', 'countries.country_name'])
            ->join('INNER JOIN', 'countries', 'countries.id = user_geo.country_id')
            ->where(['operator_geo.user_id' => $operator_id])
            ->andWhere($available === true ? 'user_geo.is_active = 1' : '')
            ->asArray()
            ->all();

        if ($cc_api === false) return ArrayHelper::map($geo, 'country_id', 'country_name');
        else return $geo;

    }

    public static function getPhoneLines()
    {
        $line_owner_id = Yii::$app->user->identity->getOwnerId();

        $search = new PhoneLinesSearch();

        if ($line_owner_id != null) $search->owner_id = $line_owner_id;
        $lines_search = $search->search([])->query->all();

        $lines = [];
        foreach ($lines_search as $line){
            $lines[$line->id] = $line->line . ' : ' . $line->owner_name . ' : ' . $line->country_name;
        }

        return $lines;
    }


    public static function getOperatorLines(){

    }

    public static function getStatuses()
    {
        return [
            self::STATUS_ONLINE => 'Online',
            self::STATUS_CALLING => 'Calling',
            self::STATUS_ONPAUSE => 'Pause',
            self::STATUS_OFFLINE => 'Offline',
        ];
    }

}