<?php

namespace crm\forms;

use Yii;
use \yii\base\Model;

/**
 * OfferForm model
 */
class OfferForm extends Model
{
    public $product_id;
    public $owner_id;
    public $product_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_id', 'product_id', 'base_stock', 'created_at', 'created_by', 'updated_by', 'base_item_cost', 'base_lead_cost'], 'required'],
            [['offer_id', 'product_id', 'base_stock', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'string', 'max' => 255],
            [['created_at', 'updated_at'], 'safe'],
            [['base_item_cost', 'base_lead_cost'], 'number'],
            ['offer_id', 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'offer_id' => Yii::t('app', 'Offer ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'base_stock' => Yii::t('app', 'Base Stock'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'base_item_cost' => Yii::t('app', 'Base Item Cost'),
            'base_lead_cost' => Yii::t('app', 'Base Lead Cost'),
        ];
    }
}