<?php

namespace common\models\offer\targets\advert;

use common\models\offer\targets\advert\sku\{
    TargetAdvertSku, TargetAdvertSkuRules
};
use common\models\order\Order;
use common\models\stock\Stock;
use common\modules\user\models\tables\User;
use common\models\BaseModel;
use Yii;

/**
 * This is the model class for table "target_advert".
 *
 * @property integer $target_advert_id
 * @property integer $target_advert_group_id
 * @property integer $advert_id
 * @property integer $stock_id
 * @property integer $active
 * @property integer $pay_online
 *
 * @property Order[] $orders
 * @property User $advert
 * @property Stock $stock
 * @property TargetAdvertGroup $targetAdvertGroup
 * @property TargetAdvertDailyRest $targetAdvertDailyRest
 * @property TargetAdvertGroupRules[] $targetAdvertGroupRules
 * @property TargetAdvertSku[] $targetAdvertSku
 * @property TargetAdvertSkuRules[] $targetAdvertSkuRules
 */
class TargetAdvert extends BaseModel
{
    public $created_at = false;
    public $created_by = false;
    public $updated_at = false;
    public $updated_by = false;

    public $views;
    public $uniques;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'target_advert';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['target_advert_group_id', 'advert_id'], 'required'],
            [['target_advert_group_id', 'advert_id', 'stock_id', 'active', 'pay_online'], 'integer'],
            [['target_advert_group_id', 'advert_id'], 'unique', 'targetAttribute' => ['target_advert_group_id', 'advert_id'], 'message' => 'The combination of Target Advert Group ID and Advert ID has already been taken.'],
            [['advert_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['advert_id' => 'id']],
            [['stock_id'], 'exist', 'skipOnError' => true, 'targetClass' => Stock::className(), 'targetAttribute' => ['stock_id' => 'stock_id']],
            [['target_advert_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => TargetAdvertGroup::className(), 'targetAttribute' => ['target_advert_group_id' => 'target_advert_group_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'target_advert_id' => Yii::t('app', 'Target Advert ID'),
            'target_advert_group_id' => Yii::t('app', 'Target Advert Group ID'),
            'advert_id' => Yii::t('app', 'Advert ID'),
            'stock_id' => Yii::t('app', 'Stock ID'),
            'active' => Yii::t('app', 'Active'),
            'pay_online' => Yii::t('app', 'Pay Online'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['target_advert_id' => 'target_advert_id']);
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
    public function getStock()
    {
        return $this->hasOne(Stock::className(), ['stock_id' => 'stock_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetAdvertGroup()
    {
        return $this->hasOne(TargetAdvertGroup::className(), ['target_advert_group_id' => 'target_advert_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetAdvertDailyRest()
    {
        return $this->hasOne(TargetAdvertDailyRest::className(), ['target_advert_id' => 'target_advert_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetAdvertGroupRules()
    {
        return $this->hasMany(TargetAdvertGroupRules::className(), ['target_advert_group_id' => 'target_advert_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetAdvertSku()
    {
        return $this->hasMany(TargetAdvertSku::className(), ['target_advert_id' => 'target_advert_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetAdvertSkuRules()
    {
        return $this->hasMany(TargetAdvertSkuRules::className(), ['target_advert_id' => 'target_advert_id']);
    }

    public static function getGroupAdvertsByGroupId($target_advert_group_id)
    {
        return self::find()
            ->select('advert_id')
            ->where(['target_advert_group_id' => $target_advert_group_id])
            ->asArray()
            ->all();
    }

    /**
     * @param $target_advert_group_id
     * @return array|TargetAdvertSku[]|TargetAdvertSkuRules[]|TargetAdvert[]|TargetAdvertGroup[]|TargetAdvertGroupRules[]|TargetAdvertView[]|\yii\db\ActiveRecord[]
     */
    public static function getGroupAdvertsNotifyByGroupId($target_advert_group_id)
    {
        return self::find()
            ->select([
                "CONCAT(id, \" \", \"-\" , \" \", username) AS advert"
            ])
            ->join('JOIN', 'user', 'advert_id = id')
            ->where(['target_advert_group_id' => $target_advert_group_id, 'active' => 1])
            ->asArray()
            ->all();
    }
}