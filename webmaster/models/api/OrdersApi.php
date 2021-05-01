<?php

namespace webmaster\models\api;

use common\modules\user\models\tables\User;
use Yii;
use yii\db\ActiveRecord;

class OrdersApi extends ActiveRecord
{
    public static function tableName()
    {
        return '{{orders_api}}';
    }

    public function rules()
    {
        return [
            [['', 'api_key', 'flow-key', ''], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'User ID'),
            'api_key' => Yii::t('app', 'User Api Key'),
        ];
    }

    public function  getUser(){
        return $this->hasMany(User::className(), ['id' => 'user_id']);
    }
}