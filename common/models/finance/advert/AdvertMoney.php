<?php

namespace common\models\finance\advert;

use common\models\finance\Currency;
use common\modules\user\models\tables\User;
use yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "advert_money".
 *
 * @property integer $advert_id
 * @property double $money
 * @property integer $currency_id
 * @property string $last_entrance_datetime
 *
 * @property User $advert
 * @property Currency $currency
 */
class AdvertMoney extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'advert_money';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['advert_id', 'money', 'currency_id'], 'required'],
            [['advert_id', 'currency_id'], 'integer'],
            [['money'], 'number'],
            [['last_entrance_datetime'], 'safe'],
            [['advert_id'], 'unique'],
            [['advert_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['advert_id' => 'id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'currency_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'advert_id' => Yii::t('app', 'Advert ID'),
            'money' => Yii::t('app', 'Money'),
            'currency_id' => Yii::t('app', 'Currency ID'),
            'last_entrance_datetime' => Yii::t('app', 'Last Entrance Datetime'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvert()
    {
        return $this->hasOne(User::className(), ['id' => 'advert_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['currency_id' => 'currency_id']);
    }


}