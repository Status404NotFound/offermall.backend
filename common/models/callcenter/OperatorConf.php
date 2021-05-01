<?php

namespace common\models\callcenter;


use common\services\callcenter\OperatorSettingsSrv;
use Yii;

/**
 * This is the model class for table "operator_conf".
 *
 * @property integer $operator_id
 * @property integer $call_mode
 * @property integer $status
 * @property integer $sip
 * @property integer $channel
 */
class OperatorConf extends \yii\db\ActiveRecord
{
    public $languages;
    public $offers;
    public $countries;
    public $username;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'operator_conf';
    }

    /**
     * @inheritdoc
     */


    public function rules()
    {
        return [
            [[ 'status', 'channel', 'operator_id',], 'integer'],
            ['call_mode', 'boolean'],
            ['sip', 'integer', 'min' => 999, 'max' => 9999],
            ['operator_id', 'unique'],
//            [['sip', 'channel'], 'unique'],
            ['call_mode', 'required'],
            [
                ['sip', 'channel'], 'required', 'whenClient' =>
                function($model){
                    return $model->call_mode == 0;
                },
                'enableClientValidation' => false,
            ],
            [['offers'], 'safe']

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'operator_id' => 'Operator ID',
            'call_mode' => 'Call Mode',
            'status' => 'Status',
            'sip' => 'Sip',
            'channel' => 'Channel',
        ];
    }
}
