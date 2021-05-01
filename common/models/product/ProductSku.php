<?php

namespace common\models\product;

use common\helpers\FishHelper;
use common\models\BaseModel;
use common\models\geo\Geo;
use common\models\log\orderSku\OrderSkuLog;
use common\models\offer\targets\advert\sku\TargetAdvertSku;
use common\models\offer\targets\advert\sku\TargetAdvertSkuRules;
use common\models\order\Order;
use common\models\order\OrderSku;
use common\models\stock\Stock;
use common\models\stock\StockSku;
use common\modules\user\models\tables\User;
use Yii;

/**
 * This is the model class for table "product_sku".
 *
 * @property integer $sku_id
 * @property integer $product_id
 * @property string $sku_name
 * @property string $sku_alias
 * @property string $color
 * @property string $img
 * @property string $description
 * @property integer $active
 * @property string $updated_at
 * @property string $common_sku_name
 * @property integer $updated_by
 * @property integer $advert_id
 * @property integer $geo_id
 *
 * @property OrderSku[] $orderSkus
 * @property Order[] $orders
 * @property OrderSkuLog[] $orderSkuLogs
 * @property User $advert
 * @property Geo $geo
 * @property Product $product
 * @property User $updatedBy
 * @property StockSku[] $stockSkus
 * @property Stock[] $stocks
 * @property TargetAdvertSku[] $targetAdvertSkus
 * @property TargetAdvertSkuRules[] $targetAdvertSkuRules
 */
class ProductSku extends BaseModel
{
    public $created_by = false;
    public $created_at = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_sku';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'sku_name', 'sku_alias'], 'required'],
            [['product_id', 'active', 'updated_by', 'advert_id', 'geo_id'], 'integer'],
            [['description'], 'string'],
            [['updated_at'], 'safe'],
            [['sku_name', 'sku_alias', 'color', 'img', 'common_sku_name'], 'string', 'max' => 255],
            [['sku_name'], 'unique'],
            [['advert_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['advert_id' => 'id']],
            [['geo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Geo::className(), 'targetAttribute' => ['geo_id' => 'geo_id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'product_id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'sku_id' => Yii::t('app', 'Sku ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'sku_name' => Yii::t('app', 'Sku Name'),
            'sku_alias' => Yii::t('app', 'Sku Alias'),
            'color' => Yii::t('app', 'Color'),
            'img' => Yii::t('app', 'Img'),
            'description' => Yii::t('app', 'Description'),
            'active' => Yii::t('app', 'Active'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'advert_id' => Yii::t('app', 'Advert ID'),
            'geo_id' => Yii::t('app', 'Geo ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderSkus()
    {
        return $this->hasMany(OrderSku::className(), ['sku_id' => 'sku_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['order_id' => 'order_id'])->viaTable('order_sku', ['sku_id' => 'sku_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderSkuLogs()
    {
        return $this->hasMany(OrderSkuLog::className(), ['sku_id' => 'sku_id']);
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
    public function getGeo()
    {
        return $this->hasOne(Geo::className(), ['geo_id' => 'geo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['product_id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockSkus()
    {
        return $this->hasMany(StockSku::className(), ['sku_id' => 'sku_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStocks()
    {
        return $this->hasMany(Stock::className(), ['stock_id' => 'stock_id'])->viaTable('stock_sku', ['sku_id' => 'sku_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetAdvertSkus()
    {
        return $this->hasMany(TargetAdvertSku::className(), ['sku_id' => 'sku_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetAdvertSkuRules()
    {
        return $this->hasMany(TargetAdvertSkuRules::className(), ['sku_id' => 'sku_id']);
    }

    /**
     * @inheritdoc
     * @return ProductSkuQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProductSkuQuery(get_called_class());
    }

}
