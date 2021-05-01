<?php

namespace common\models\finance;

use Yii;
use common\models\BaseModel;

/**
 * This is the model class for table "closed_financial_periods".
 *
 * @property int $period_id
 * @property string $date
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 */
class ClosedFinancialPeriods extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'closed_financial_periods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['date'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'period_id' => Yii::t('app', 'Period ID'),
            'date' => Yii::t('app', 'Date'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }
}