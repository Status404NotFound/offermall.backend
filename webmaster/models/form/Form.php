<?php

namespace webmaster\models\form;

use common\models\landing\Landing;
use phpDocumentor\Reflection\Types\Self_;
use \yii\db\ActiveRecord;

class Form extends ActiveRecord
{

    /**
     * @var mixed|null
     */

    public static function tableName()
    {
        return '{{wm_form}}';
    }

    public function rules()
    {
        return [
            [['wm_id', 'flow_id', 'offer_id', 'url'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Form ID',
            'wm_id' => 'Webmaster ID',
            'flow_id' => 'Flow ID',
            'form_landing_id' => 'Form Landing ID',
            'landing_id' => 'Landing ID',
            'url' => 'Url',
        ];
    }

    public static function getFormsByUserId($id)
    {
        return self::find()->where([
            'wm_id' => $id,
        ])->all();
    }

    public static function checkExistByUrl($url){
        if(isset(self::find()->where(['url' => $url])->one()->id)){
            return true;
        }
        return false;
    }

    public static function getFormByUrl($url){
        return self::find()->where(['url' => $url])->one();
    }

    public static function getFormByLandingID($landing_id)
    {
        return self::find()->where(['landing_id' => $landing_id])->one();
    }

    public static function getFormById($id){
        return self::findOne($id);
    }

    public static function getDomainNameByUrl($url){
        return str_replace(['http://', 'https://'], '', $url);
    }
}