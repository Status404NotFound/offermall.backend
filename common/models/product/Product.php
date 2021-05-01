<?php

namespace common\models\product;

use common\helpers\FishHelper;
use common\models\BaseModel;
use common\models\offer\Offer;
use common\models\offer\OfferProduct;
use common\modules\user\models\tables\User;
use Yii;

/**
 * Product model
 *
 * @property integer $product_id
 * @property integer $category
 * @property string $product_name
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $description
 * @property string $img
 * @property boolean $visible
 *
 * @property ProductSku[] $product_sku
 */
class Product extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['product_name'], 'required'],
            [['created_by', 'updated_by', 'category'], 'integer'],
            [['product_name', 'img'], 'string', 'max' => 255],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
            ['visible', 'boolean'],
            [['visible', 'category'], 'default', 'value' => 1],
            [['created_at', 'updated_at', 'description'], 'safe'],
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
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'category' => Yii::t('app', 'Category'),
            'description' => Yii::t('app', 'Description'),
            'img' => Yii::t('app', 'Image'),
            'visible' => Yii::t('app', 'Visible'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct_sku()
    {
        return self::hasMany(ProductSku::className(), ['product_id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOffer_products()
    {
        return $this->hasMany(OfferProduct::className(), ['product_id' => 'product_id']);
    }

    /**
     * @return array|Product[]
     */
    public static function getAll()
    {
        return self::find()->all();
    }

    /**
     * @inheritdoc
     * @return ProductQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProductQuery(get_called_class());
    }

}