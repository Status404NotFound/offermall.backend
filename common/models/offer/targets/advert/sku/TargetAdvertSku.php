<?php

namespace common\models\offer\targets\advert\sku;

use common\helpers\FishHelper;
use common\models\{
    BaseModel, offer\Offer, offer\OfferProduct, offer\targets\advert\TargetAdvert, order\Order, product\ProductSku
};
use common\modules\user\models\tables\User;
use common\services\offer\exceptions\AdvertServiceException;
use common\services\webmaster\ArrayHelper;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "target_advert_sku".
 *
 * @property integer $target_advert_sku_id
 * @property integer $target_advert_id
 * @property integer $sku_id
 * @property double $base_cost
 * @property double $exceeded_cost
 * @property integer $is_upsale
 * @property integer $is_bookkeeping
 * @property integer $use_sku_cost_rules
 * @property integer $use_extended_rules
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @property ProductSku $sku
 * @property TargetAdvert $targetAdvert
 * @property User $updatedBy
 * @property TargetAdvertSkuRules[] $targetAdvertSkuRules
 */
class TargetAdvertSku extends BaseModel
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
            [['target_advert_id'], 'required'],
            [['target_advert_id', 'is_upsale', 'is_bookkeeping', 'use_sku_cost_rules', 'use_extended_rules', 'updated_by'], 'integer'],
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetAdvertSkuRules()
    {
        return $this->hasMany(TargetAdvertSkuRules::className(), ['target_advert_sku_id' => 'target_advert_sku_id']);
    }

    public static function findForOrderCommission($target_advert_id)
    {
        return self::find()
            ->select([
                'target_advert_sku_id', 'sku_id',
                'base_cost', 'exceeded_cost',
                'use_sku_cost_rules', 'use_extended_rules',
                'is_upsale'
            ])
            ->where(['target_advert_id' => $target_advert_id])
            ->indexBy('sku_id')
            ->asArray()
            ->all();
    }

    public static function findAdvertSku($target_advert_id)
    {
        if (!$targetAdvert = TargetAdvert::findOne($target_advert_id))
            throw new AdvertServiceException('TargetAdvert with id = ' . $target_advert_id . ' NotFound.');

        $targetAdvertSku = self::find()
            ->select(['target_advert_sku.sku_id', 'PS.sku_name'])
            ->join('LEFT JOIN', 'product_sku PS', 'PS.sku_id = target_advert_sku.sku_id')
            ->andWhere(['target_advert_sku.target_advert_id' => $target_advert_id])
            ->asArray()->all();

        if (count($targetAdvertSku) === 1 && $targetAdvertSku[0]['sku_id'] === null) {
            $product_ids = OfferProduct::find()->select('product_id')
                ->where(['offer_id' => $targetAdvert->targetAdvertGroup->advertOfferTarget->offer_id])->asArray()->all();

            $targetAdvertSku = ProductSku::find()
                ->select(['sku_id', 'sku_name'])
                ->where([
                    'advert_id' => $targetAdvert->advert_id,
                    'geo_id' => $targetAdvert->targetAdvertGroup->advertOfferTarget->geo_id,
                    'product_id' => ArrayHelper::map($product_ids, 'product_id', 'product_id')
                ])->asArray()->all();
        }
        return $targetAdvertSku;
    }

    public static function findOfferSkuList($offer_id)
    {
//        $offer = Offer::findOne(['offer_id' => $offer_id]);
        return self::find()
            ->select(['target_advert_sku.sku_id', 'PS.sku_name'])
            ->join('INNER JOIN', 'product_sku PS', 'PS.sku_id = target_advert_sku.sku_id')
            ->andWhere(['PS.product_id' => Offer::findProductIds($offer_id)])
            ->asArray()
            ->all();
    }
}