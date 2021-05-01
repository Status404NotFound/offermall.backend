<?php
namespace callcenter\components\ccApi\core;

use yii;
use yii\base\Model;
use linslin\yii2\curl\Curl;
use yii\helpers\ArrayHelper;
/**
 * Class CcApi
 * @package app\models\ccApi\core
 */
class CcApi extends Model
{
    protected $api_key;
    protected $responseType = 'json';

    const ACTION_MAKE_CALL = 1;
    const ACTION_RECORD_LIST = 2;
    const ACTION_ORDER_RECORDS = 3;
    const ACTION_ORDER_HISTORY = 4;
    const ACTION_CALL_HISTORY = 5;
    const ACTION_SIPLIST = 6;
    const ACTION_OPERATOR_LIST = 7;

    /**
     * @var $curl Curl;
     */
    private $curl;
    /**
     * @var
     */
//    public $action;

    protected $apiUrl;

    public function init()
    {
        parent::init();
        $this->curl = new Curl();
        $this->apiUrl = Yii::$app->params['callCenterApi'];
    }

    /**
     * @return array
     */
    public function allowedResponseTypes()
    {
        return ['json', 'xml'];
    }

    /**
     * @param $type string
     * $type can be either 'json' or 'xml'
     * @return array
     */
    static public function getApiActions($type = 'json')
    {
        $actions = [
            self::ACTION_MAKE_CALL => 'makeCall',
            self::ACTION_RECORD_LIST => 'recordList',
            self::ACTION_ORDER_RECORDS => 'orderRecords',
            self::ACTION_ORDER_HISTORY => 'orderHistory',
            self::ACTION_CALL_HISTORY => 'callHistory',
            self::ACTION_SIPLIST => 'sipList.json',
            self::ACTION_OPERATOR_LIST => 'operatorHistory',
        ];

        switch ($type){
            case $type == 'json':
                foreach($actions as $key => $action)
                {
                    $actions[$key] = $action.'.json';
                }
                break;
            case $type == 'xml':
                foreach($actions as $key => $action)
                {
                    $actions[$key] = $action.'.xml';
                }
                break;
        }

        return $actions;
    }



    /**
     * @param $action
     * @param null|array $requestParams
     * @return string
     */
    public function addParams($action, $requestParams = null)
    {
        if(!empty($requestParams))
        {

            if(self::load($requestParams, ''))
            {
                $params = self::arrayToGetParams(self::getAttributes());
                $request = $action.$params;
                return $request;

            }else{
                return $action;
            }
        }else{
            return $action;
        }
    }

    static function arrayToGetParams($params)
    {
        if (empty($params) || !ArrayHelper::isAssociative($params)) {
            return '';
        }

        $pairs = [];
        foreach ($params as $key => $val) $pairs[] = $key . '=' . $val;
        $url = '?' . implode('&', $pairs);

        return $url;
    }

    /**
     * @param $requestParams
     * @return mixed
     */
    public function doRequest($requestParams)
    {
        $request = $this->addParams($this->getApiActions($this->responseType)[$this->api_key], $requestParams);
        return $this->curl->get($this->apiUrl.$request);
    }

    /**
     * @param $type
     * @return bool
     */
    public function setResponseType($type)
    {
        if(in_array($type, $this->allowedResponseTypes()))
        {
            $this->responseType = $type;
            return true;
        }else{
            return false;
        }
    }




}