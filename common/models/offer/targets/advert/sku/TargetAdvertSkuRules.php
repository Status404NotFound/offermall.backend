<?php

namespace common\models\offer\targets\advert\sku;

use common\helpers\FishHelper;
use common\models\{
    BaseModel, offer\targets\advert\TargetAdvert, product\ProductSku
};
use common\modules\user\models\tables\User;
use Yii;

/**
 * This is the model class for table "target_advert_sku_rules".
 *
 * @property integer $target_advert_sku_rule_id
 * @property integer $target_advert_id
 * @property integer $target_advert_sku_id
 * @property integer $sku_id
 * @property integer $amount
 * @property double $cost
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @property ProductSku $sku
 * @property TargetAdvert $targetAdvert
 * @property TargetAdvertSku $targetAdvertSku
 * @property User $updatedBy
 */
class TargetAdvertSkuRules extends BaseModel
{
    public $created_at = false;
    public $created_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'target_advert_sku_rules';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['target_advert_id', 'target_advert_sku_id', 'amount', 'cost'], 'required'],
            [['target_advert_id', 'target_advert_sku_id', 'sku_id', 'amount', 'updated_by'], 'integer'],
            [['cost'], 'number'],
            [['updated_at'], 'safe'],
            [['target_advert_id', 'sku_id', 'amount'], 'unique', 'targetAttribute' => ['target_advert_id', 'sku_id', 'amount'], 'message' => 'The combination of Target Advert ID, Sku ID and Amount has already been taken.'],
            [['sku_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductSku::className(), 'targetAttribute' => ['sku_id' => 'sku_id']],
            [['target_advert_id'], 'exist', 'skipOnError' => true, 'targetClass' => TargetAdvert::className(), 'targetAttribute' => ['target_advert_id' => 'target_advert_id']],
            [['target_advert_sku_id'], 'exist', 'skipOnError' => true, 'targetClass' => TargetAdvertSku::className(), 'targetAttribute' => ['target_advert_sku_id' => 'target_advert_sku_id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'target_advert_sku_rule_id' => Yii::t('app', 'Target Advert Sku Rule ID'),
            'target_advert_id' => Yii::t('app', 'Target Advert ID'),
            '$target_advert_sku_id' => Yii::t('app', 'Target Advert Sku ID'),
            'sku_id' => Yii::t('app', 'Sku ID'),
            'amount' => Yii::t('app', 'Amount'),
            'cost' => Yii::t('app', 'Cost'),
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
    public function getTargetAdvertSku()
    {
        return $this->hasOne(TargetAdvertSku::className(), ['target_advert_sku_id' => 'target_advert_sku_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @param $target_advert_id
     * @return array|\yii\db\ActiveRecord[]
     *
     * Правила для всех ОрдерСку
     */
    public static function findAllForOrderCommission($target_advert_id)
    {
        return self::find()
            ->select(['sku_id', 'amount', 'cost'])
            ->where(['target_advert_id' => $target_advert_id])
            ->asArray()
            ->all();
    }

    /**
     * @param $target_advert_id
     * @param $sku_id
     * @return array|\yii\db\ActiveRecord[]
     *
     * Правила для конкретного ОрдерСку
     */
    public static function findSkuRulesForOrderCommission($target_advert_id, $sku_id)
    {
        $rules = self::find()
            ->select(['sku_id', 'amount', 'cost'])
            ->where(['target_advert_id' => $target_advert_id]);

        if ($sku_id !== null)
            $rules->andWhere(['sku_id' => $sku_id]);

        $rules = $rules->asArray()->all();
        return $rules;
    }
}