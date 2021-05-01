<?php

namespace common\models\stock;

use common\models\BaseModel;
use common\models\offer\targets\advert\AdvertOfferTarget;
use common\models\product\ProductSku;
use common\modules\user\models\tables\User;
use Yii;

/**
 * This is the model class for table "stock_sku".
 *
 * @property integer $stock_sku_id
 * @property integer $stock_id
 * @property integer $sku_id
 * @property integer $amount
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @property ProductSku $sku
 * @property Stock $stock
 * @property User $updatedBy
 */
class StockSku extends BaseModel
{
    public $created_at = false;
    public $created_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stock_sku';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['stock_id', 'sku_id'], 'required'],
            [['stock_id', 'sku_id', 'amount'], 'integer'],
            [['updated_at'], 'safe'],
            [['sku_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductSku::className(), 'targetAttribute' => ['sku_id' => 'sku_id']],
            [['stock_id'], 'exist', 'skipOnError' => true, 'targetClass' => Stock::className(), 'targetAttribute' => ['stock_id' => 'stock_id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'stock_sku_id' => Yii::t('app', 'Stock ProductSku ID'),
            'stock_id' => Yii::t('app', 'Stock ID'),
            'sku_id' => Yii::t('app', 'ProductSku ID'),
            'amount' => Yii::t('app', 'Amount'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSku()
    {
        return $this->hasOne(ProductSku::className(), ['sku_id' => 'sku_id']);
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
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

//    /**
//     * @inheritdoc
//     * @return StockQuery the active query used by this AR class.
//     */
//    public static function find()
//    {
//        return new StockQuery(get_called_class());
//    }
}
