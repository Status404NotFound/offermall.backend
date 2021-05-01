<?php


namespace webmaster\models\form;


use yii\db\ActiveRecord;

class FormLanding extends ActiveRecord
{
    public static function tableName()
    {
        return '{{wm_form_landing}}';
    }

    public function rules()
    {
        return [
            [['landing_id', 'url', 'offer_id'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'landing_id' => 'ID',
            'name' => 'Name',
            'url' => 'Url',
            'offer_id' => 'Offer ID'
        ];
    }

    public static function getFormLandingByUrl($url){
        return self::find()->where(['url' => $url])->orWhere(['url' => $url.'/'])->one();
    }

    public static function checkExistByUrl($url){
        return self::find()->where(['url' => $url])->one() ? true : false;
    }
}