<?php

namespace common\models\stock;

use common\models\BaseModel;
use common\models\stock\AdvertOfferTarget;
use common\models\stock\Stock;
use \common\modules\user\models\tables\User;
use Yii;
use yii\base\Module;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "stock_traffic".
 *
 * @property integer $tranfer_id
 * @property integer $stock_id_from
 * @property integer $stock_id_to
 * @property integer $is_new
 * @property integer $sku_id
 * @property integer $order_id
 * @property integer $amount
 * @property integer $created_by
 * @property string $datetime
 *
 * @property User $createdBy
 * @property Stock $stockIdFrom
 * @property Stock $stockIdTo
 */
class StockTraffic extends BaseModel
{
    public $created_at = false;
    public $updated_at = false;
    public $updated_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stock_traffic';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_new', 'sku_id', 'amount'], 'required'],
            [['stock_id_from', 'stock_id_to', 'is_new', 'sku_id', 'amount', 'order_id'], 'integer'],
            [['datetime'], 'safe'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'user_id']],
            [['stock_id_from'], 'exist', 'skipOnError' => true, 'targetClass' => Stock::className(), 'targetAttribute' => ['stock_id_from' => 'stock_id']],
            [['stock_id_to'], 'exist', 'skipOnError' => true, 'targetClass' => Stock::className(), 'targetAttribute' => ['stock_id_to' => 'stock_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'stock_id_from' => Yii::t('app', 'Stock Id From'),
            'stock_id_to' => Yii::t('app', 'Stock Id To'),
            'order_id' => Yii::t('app', 'Order Id'),
            'is_new_shipment' => Yii::t('app', 'Is New Shipment'),
            'sku_id' => Yii::t('app', 'ProductSku ID'),
            'amount' => Yii::t('app', 'Amount'),
            'created_by' => Yii::t('app', 'Created By'),
            'datetime' => Yii::t('app', 'Datetime'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['user_id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockIdFrom()
    {
        return $this->hasOne(Stock::className(), ['stock_id' => 'stock_id_from']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockIdTo()
    {
        return $this->hasOne(Stock::className(), ['stock_id' => 'stock_id_to']);
    }
}
