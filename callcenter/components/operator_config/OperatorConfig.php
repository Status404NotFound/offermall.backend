<?php
/**
 * Created by PhpStorm.
 * User: ihor
 * Date: 17.03.17
 * Time: 12:44
 */

namespace callcenter\components\operator_config;


use common\services\callcenter\auto\AutoModeSrv;
use common\models\callcenter\CallList;
use common\models\callcenter\OperatorConf;
use callcenter\services\operator_activity\OperatorActivityService;
//use common\models\callcenter\OperatorFines;
use common\models\callcenter\OperatorFine;
use common\modules\user\models\tables\User;
use common\services\callcenter\call_list\CallListService;
use common\services\callcenter\call_list\LeadStatus;
use common\services\callcenter\OperatorSettingsSrv;
use yii\base\Component;
use Yii;
use yii\base\Exception;

class OperatorConfig extends Component
{
    /**
     * operator properties
     */
    public $operator_conf;
    public $is_config_operator;

    public $username;
    public $sip;
    public $channel;
    public $call_mode;
    public $status;
    public $id;
    //public $language;

    public function __construct($user_id = null)
    {
        $user = !Yii::$app->user->isGuest ? Yii::$app->user->identity : User::findOne($user_id);
        $this->operator_conf = OperatorConf::find()->where(['operator_id' => $user->id])->one();
        if (!empty($this->operator_conf) && $user->role == User::ROLE_OPERATOR){
            $this->is_config_operator = true;
            $this->id = $user->id;
            $this->username = $user->username;
            $this->call_mode = $this->operator_conf->call_mode;
            $this->channel = $this->operator_conf->channel;
            $this->sip = $this->operator_conf->sip;
            $this->status = $this->operator_conf->status;
            //$this->language = $this->operator_conf->language;
        }else{
            $this->is_config_operator = false;
            throw new Exception('This user is not operator or has not any configuration!!! Please check user role and configuration!');
        }


        parent::__construct(); // TODO: Change the autogenerated stub
    }


    public function setOperatorStatus($status = OperatorSettingsSrv::STATUS_ONPAUSE)
    {

        $this->operator_conf->status = $status;
        $this->status = $status;

        if ( $this->operator_conf->save()) return true;

        return false;
    }


    public function getOperatorStatus($all = false, $array  = false){

        $statuses = array(
            OperatorSettingsSrv::STATUS_OFFLINE => 'Offline',
            OperatorSettingsSrv::STATUS_ONLINE => 'Online',
            OperatorSettingsSrv::STATUS_CALLING => 'Calling',
            OperatorSettingsSrv::STATUS_ONPAUSE => 'On pause',
        );

        if($all != false){
            return $statuses;
        }else{
            if ($array != false) return [$this->status => $statuses[$this->status]];
            else return $statuses[$this->status];
        }
    }

    public function getStatuses()
    {
        return [
            [
                'status_id' => OperatorSettingsSrv::STATUS_ONLINE,
                'status_label' => 'Online',
            ],
            [
                'status_id' => OperatorSettingsSrv::STATUS_CALLING,
                'status_label' => 'Calling',
            ],
            [
                'status_id' => OperatorSettingsSrv::STATUS_ONPAUSE,
                'status_label' => 'On Pause',
            ],
            [
                'status_id' => OperatorSettingsSrv::STATUS_OFFLINE,
                'status_label' => 'Offline',
            ],
        ];
    }

    public static function getOperators($adv_id){
        $operators = User::find()->select(['id', 'username'])->where(['parent_id' => $adv_id])->asArray()->all();
        return $operators;
    }


    public function setFine(){

        $fine = new OperatorFine();
        $fine->operator_id = $this->id;
        $fine->status_id = $fine::STATUS_PENDIG;

        if ($fine->save()) return true;
        return false;
    }



    public function takeLead($order_id, $operator_id = null){
        if (is_null($operator_id)) $operator_id = $this->id;

        $call_list = new CallListService();
        if ($call_list->reserveLeadByOperator($order_id, $operator_id)) $lead = $call_list->getOrder($order_id);
        else $lead = ['Lead already was taken'];

        try{
            $call_list->checkPendingPlanLeads();
            $call_list->checkPendingToDoLeads();
        }catch (\Exception $e){}

        return $lead;
    }

    public function getPermissions()
    {
        return [

        ];
    }

    public function generateLead(){

        $call_list = new AutoModeSrv();

        $gen_lead = $call_list->generated_order;

        if ($call_list->reserveLeadByOperator($gen_lead['order_id'], $this->id)){

            //Yii::$app->cc_api->makeCall($gen_lead);
            return $gen_lead;
        }
        else return 'Lead already was taken';

    }
}