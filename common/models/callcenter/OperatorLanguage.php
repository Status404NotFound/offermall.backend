<?php

namespace common\models\callcenter;

use Yii;

/**
 * This is the model class for table "operator_language".
 *
 * @property integer $user_id
 * @property integer $language_id
 * @property integer $is_active
 */
class OperatorLanguage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'operator_language';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'language_id', 'is_active'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'operator_id' => 'Operator ID',
            'language_id' => 'Language ID',
        ];
    }
}
