<?php

namespace common\models\finance;

use common\helpers\FishHelper;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "currency_rate_per_date".
 *
 * @property integer $rate_id
 * @property double $rate
 * @property integer $currency_id
 * @property string $date
 *
 * @property Currency $currency
 */
class CurrencyRatePerDay extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'currency_rate_per_date';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rate', 'currency_id'], 'required'],
            ['currency_id', 'integer'],
            [['rate'], 'double'],
            [['date'], 'safe'],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'currency_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rate_id' => Yii::t('app', 'Rate ID'),
            'rate' => Yii::t('app', 'Rate'),
            'currency_id' => Yii::t('app', 'Currency ID'),
            'date' => Yii::t('app', 'Date'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['currency_id' => 'currency_id']);
    }

    /**
     * @param $currency_id
     * @return float|mixed
     */
    public static function getCurrencyRate($currency_id)
    {
        /** @var CurrencyRatePerDay $rate */
        $rate = self::find()
//            ->select(['rate'])
            ->where(['currency_id' => $currency_id])
            ->orderBy('date DESC')
            ->one();
        return $rate->rate;
    }
}