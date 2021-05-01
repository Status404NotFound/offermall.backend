<?php

namespace common\models\product;

use common\helpers\FishHelper;
use common\models\BaseModel;
use Yii;

/**
 * This is the model class for table "sku_view".
 *
 * @property integer $product_id
 * @property string $product_name
 * @property integer $sku_id
 * @property string $sku_name
 * @property string $sku_alias
 * @property string $color
 *
 * @property Product $product
 */
class SkuView extends BaseModel
{
    public $created_by = false;
    public $created_at = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sku_view';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'sku_id'], 'integer'],
            [['product_id', 'product_name', 'sku_name', 'sku_alias'], 'required'],
            [['product_name', 'sku_name', 'sku_alias', 'color'], 'string', 'max' => 255],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'product_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_id' => Yii::t('app', 'Product ID'),
            'product_name' => Yii::t('app', 'Product Name'),
            'sku_id' => Yii::t('app', 'Sku ID'),
            'sku_name' => Yii::t('app', 'Sku Name'),
            'sku_alias' => Yii::t('app', 'Sku Alias'),
            'color' => Yii::t('app', 'Color'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['product_id' => 'product_id']);
    }

    public static function findListByProductId($product_id)
    {
        return self::find()->select('sku_id, sku_name')
            ->where(['product_id' => $product_id])
            ->asArray()->all();
    }

}