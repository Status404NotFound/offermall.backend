<?php


namespace webmaster\models\partners;


use yii\base\Exception;
use yii\db\ActiveRecord;

class Partner extends ActiveRecord
{
    const ACTIVE_STATUS = 1;
    const DEACTIVE_STATUS = 0;

    public static function tableName()
    {
        return '{{partner_crm}}';
    }

    public function rules()
    {
        return [
            [['advert_id', 'name', 'slug', 'class_name', 'active'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'advert_id' => 'Advert ID',
            'name' => 'Partner Name',
            'slug' => 'Our Slug',
            'class_name' => 'Class name',
            'active' => 'CRM Status'
        ];
    }

    public static function getAllPartners(): array
    {
        return self::find()->all();
    }

    public static function getAllActivePartners(): array
    {
        return self::find()->where(['active' => 1])->all();
    }
}