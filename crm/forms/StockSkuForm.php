<?php

namespace crm\forms;

use Yii;
use \yii\base\Model;

/**
 * OfferForm model
 */
class StockSkuForm extends Model
{
    public $stock_id;
    public $sku_id;
    public $count;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku_id', 'count', 'updated_by'], 'required'],
            [['sku_id', 'count', 'updated_by'], 'integer'],
            [['updated_at'], 'string', 'max' => 255],
            [['updated_at'], 'safe'],
            ['stock_id', 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'stock_id' => Yii::t('app', 'Stock ID'),
            'sku_id' => Yii::t('app', 'ProductSku ID'),
            'count' => Yii::t('app', 'Count'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }
}