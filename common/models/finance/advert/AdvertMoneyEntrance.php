<?php

namespace common\models\finance\advert;

use Yii;
use yii\db\ActiveRecord;
use common\modules\user\models\tables\User;

/**
 * This is the model class for table "advert_money_entrance".
 *
 * @property integer $advert_id
 * @property integer $old_sum
 * @property integer $sum
 * @property string $comment
 * @property integer $added_by
 * @property string $entrance_date
 * @property string $datetime
 *
 * @property User $addedBy
 * @property User $advert
 */
class AdvertMoneyEntrance extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'advert_money_entrance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['advert_id', 'sum', 'added_by'], 'required'],
            [['advert_id', 'added_by',], 'integer'],
            [['sum', 'old_sum'], 'number'],
            [['comment'], 'string', 'max' => 255],
            [['entrance_date', 'datetime'], 'safe'],
            [['added_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['added_by' => 'id']],
            [['advert_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['advert_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'advert_id' => Yii::t('app', 'Advert ID'),
            'old_sum' => Yii::t('app', 'Old Sum'),
            'sum' => Yii::t('app', 'Sum'),
            'comment' => Yii::t('app', 'Comment'),
            'added_by' => Yii::t('app', 'Added By'),
            'entrance_date' => Yii::t('app', 'Entrance Date'),
            'datetime' => Yii::t('app', 'Datetime'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'added_by']);
    }

    /**
     * @param $advert_id
     * @return array|AdvertMoney|null|ActiveRecord
     */
    public static function getAdvertMoney($advert_id)
    {
        return AdvertMoney::find()->where(['advert_id' => $advert_id])->one();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvert()
    {
        return $this->hasOne(User::className(), ['id' => 'advert_id']);
    }
}