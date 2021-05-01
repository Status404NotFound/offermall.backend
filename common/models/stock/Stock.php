<?php

namespace common\models\stock;

use common\models\BaseModel;
use common\models\geo\Geo;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\product\ProductSku;
use Yii;
use common\modules\user\models\tables\User;

/**
 * This is the model class for table "stock".
 *
 * @property integer $stock_id
 * @property integer $owner_id
 * @property string $stock_name
 * @property integer $location
 * @property integer $status
 *
 * @property User $owner
 * @property StockSku[] $stock_sku
 * @property ProductSku[] $skus
 * @property StockTraffic[] $stockTraffics
 * @property StockTraffic[] $stockTraffics0
 * @property TargetAdvert[] $targetAdverts
 */
class Stock extends BaseModel
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;
    const STATUS_ON_PAUSE = 20;

    public $created_at = false;
    public $updated_at = false;
    public $created_by = false;
    public $updated_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stock';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['owner_id', 'stock_name', 'location'], 'required'],
            [['owner_id', 'location', 'status'], 'integer'],
            [['stock_name'], 'string', 'max' => 255],
            [['stock_name'], 'unique'],
            [['owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['owner_id' => 'id']],
            [['location'], 'exist', 'skipOnError' => true, 'targetClass' => Geo::className(), 'targetAttribute' => ['location' => 'geo_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'stock_id' => Yii::t('app', 'Stock ID'),
            'owner_id' => Yii::t('app', 'Owner ID'),
            'stock_name' => Yii::t('app', 'Stock Name'),
            'location' => Yii::t('app', 'Location'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(User::className(), ['id' => 'owner_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStock_sku()
    {
        return $this->hasMany(StockSku::className(), ['stock_id' => 'stock_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSkus()
    {
        return $this->hasMany(ProductSku::className(), ['sku_id' => 'sku_id'])->viaTable('stock_sku', ['stock_id' => 'stock_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockTraffics()
    {
        return $this->hasMany(StockTraffic::className(), ['stock_id_from' => 'stock_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockTraffics0()
    {
        return $this->hasMany(StockTraffic::className(), ['stock_id_to' => 'stock_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetAdverts()
    {
        return $this->hasMany(TargetAdvert::className(), ['stock_id' => 'stock_id']);
    }

    /**
     * @inheritdoc
     * @return StockQuery the active query` used by this AR class.
     */
    public static function find()
    {
        return new StockQuery(get_called_class());
    }
}
