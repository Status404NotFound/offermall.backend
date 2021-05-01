<?php

namespace webmaster\models\api;

use common\modules\user\models\tables\User;
use Yii;
use yii\db\ActiveRecord;

class UserApi extends ActiveRecord
{
    const ACTIVE_API_KEY_STATUS = 1;
    const INACTIVE_API_KEY_STATUS = 0;

    public static function tableName()
    {
        return '{{wm_api}}';
    }

    public function rules()
    {
        return [
            [['user_id', 'api_key', 'status'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'User ID'),
            'api_key' => Yii::t('app', 'User Api Key'),
            'status' => Yii::t('app', 'Status Api Key'),
        ];
    }

    public static function getUserApiById($id){
        return self::find()->where(['user_id' => $id])->asArray()->all();
    }

    public static function getUserApiByApiKey($apiKey){
        return self::find()->where(['api_key' => $apiKey])->one();
    }

    public static function getUserApiByStatus($status){
        if($status == self::ACTIVE_API_KEY_STATUS){
            return self::find()->where(['status' => self::ACTIVE_API_KEY_STATUS])->asArray()->all();
        }
        else{
            return self::find()->where(['status' => self::INACTIVE_API_KEY_STATUS])->asArray()->all();
        }
    }
}