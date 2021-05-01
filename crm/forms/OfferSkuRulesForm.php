<?php

namespace crm\forms;

use common\models\offer\Offer;
use common\models\sku\ProductSku;
use common\models\User;
use Yii;
use \yii\base\Model;

/**
 * OfferForm model
 */
class OfferForm extends Model
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku_id', 'count', 'cost', 'created_at', 'created_by', 'updated_by'], 'required'],
            [['sku_id', 'count', 'created_by', 'updated_by'], 'integer'],
            [['cost'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'user_id']],
            [['offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Offer::className(), 'targetAttribute' => ['offer_id' => 'offer_id']],
            [['sku_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductSku::className(), 'targetAttribute' => ['sku_id' => 'sku_id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'offer_id' => Yii::t('app', 'Offer ID'),
            'sku_id' => Yii::t('app', 'ProductSku ID'),
            'count' => Yii::t('app', 'Count'),
            'cost' => Yii::t('app', 'Cost'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }
}