<?php

namespace common\models\offer;

use common\models\BaseModel;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\product\ProductSku;
use \common\modules\user\models\tables\User;
use Yii;

/**
 * This is the model class for table "target_advert_sku".
 *
 * @property integer $target_advert_sku_id
 * @property integer $target_advert_id
 * @property integer $sku_id
 *
 * @property double $base_cost
 * @property double $exceeded_cost
 *
 * @property integer $is_upsale
 * @property integer $is_bookkeeping
 *
 * @property integer $use_sku_cost_rules
 * @property integer $use_extended_rules
 *
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @property ProductSku $sku
 * @property TargetAdvert $targetAdvert
 * @property User $updatedBy
 */
class OfferSku extends BaseModel
{
    public $created_at = false;
    public $created_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'target_advert_sku';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['target_advert_id', 'base_cost'], 'required'],
            [['target_advert_id', 'sku_id', 'is_upsale', 'is_bookkeeping', 'use_sku_cost_rules', 'use_extended_rules', 'updated_by'], 'integer'],
            [['base_cost', 'exceeded_cost'], 'number'],
            [['updated_at'], 'safe'],
            [['sku_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductSku::className(), 'targetAttribute' => ['sku_id' => 'sku_id']],
            [['target_advert_id'], 'exist', 'skipOnError' => true, 'targetClass' => TargetAdvert::className(), 'targetAttribute' => ['target_advert_id' => 'target_advert_id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'target_advert_sku_id' => Yii::t('app', 'Target Advert Sku ID'),
            'target_advert_id' => Yii::t('app', 'Target Advert ID'),
            'sku_id' => Yii::t('app', 'Sku ID'),
            'base_cost' => Yii::t('app', 'Base Cost'),
            'exceeded_cost' => Yii::t('app', 'Exceeded Cost'),
            'is_upsale' => Yii::t('app', 'Is Upsale'),
            'is_bookkeeping' => Yii::t('app', 'Is Bookkeeping'),
            'use_sku_cost_rules' => Yii::t('app', 'Use Sku Cost Rules'),
            'use_extended_rules' => Yii::t('app', 'Use Extended Rules'),
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
    public function getTargetAdvert()
    {
        return $this->hasOne(TargetAdvert::className(), ['target_advert_id' => 'target_advert_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }
}