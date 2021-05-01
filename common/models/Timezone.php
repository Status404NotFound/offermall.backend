<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "timezone_table".
 *
 * @property integer $id
 * @property string $designation
 * @property string $names
 * @property string $timezone
 */
class Timezone extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'timezone_table';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['designation', 'names', 'timezone'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'designation' => 'Designation',
            'names' => 'Names',
            'timezone' => 'Timezone',
        ];
    }
}
