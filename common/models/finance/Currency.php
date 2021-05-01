<?php

namespace common\models\finance;

use common\models\geo\Countries;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "currency".
 *
 * @property integer $id
 * @property integer $currency_id
 * @property string $currency_name
 * @property string $currency_code
 * @property integer $country_id
 *
 * @property Countries $country
 */
class Currency extends ActiveRecord
{
    const USD = 1;
    const UAH = 2;
    const AED = 3;
    const BHD = 4;
    const SAR = 5;
    const IQD = 6;
    const OMR = 7;
    const JOD = 8;
    const KWD = 9;
    const KES = 10;
    const QAR = 11;
    const NGN = 12;
    const IRR = 13;
    const INR = 14;
    const EGP = 15;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'currency';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['currency_id', 'currency_name', 'currency_code'], 'required'],
            [['currency_id', 'country_id'], 'integer'],
            [['currency_name'], 'string', 'max' => 255],
            [['currency_code'], 'string', 'max' => 7],
            [['currency_id'], 'unique'],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'currency_id' => Yii::t('app', 'Currency ID'),
            'currency_name' => Yii::t('app', 'Currency Name'),
            'currency_code' => Yii::t('app', 'Currency Code'),
            'country_id' => Yii::t('app', 'Country ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Countries::className(), ['id' => 'country_id']);
    }

    public static function list()
    {
        return self::find()
            ->select('currency_id, currency_name')
            ->asArray()
            ->all();
    }

    public static function indexedList()
    {
        return self::find()
            ->select(['currency_id', 'currency_name'])
            ->indexBy('currency_id')
            ->asArray()
            ->all();
    }
}