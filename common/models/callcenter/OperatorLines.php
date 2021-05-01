<?php

namespace common\models\callcenter;

use Yii;

/**
 * This is the model class for table "operator_lines".
 *
 * @property integer $id
 * @property integer $line_id
 * @property integer $operator_id
 * @property integer $is_active
 * @property string $created_at
 * @property string $updated_at
 */
class OperatorLines extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'operator_lines';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'line_id', 'operator_id', 'is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'line_id' => Yii::t('app', 'Line ID'),
            'operator_id' => Yii::t('app', 'Operator ID'),
            'is_active' => Yii::t('app', 'Is Active'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
