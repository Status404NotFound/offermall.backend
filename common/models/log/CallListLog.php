<?php

namespace common\models\log;

use Yii;

/**
 * This is the model class for table "call_list_log".
 *
 * @property integer $id
 * @property integer $row_id
 * @property integer $user_id
 * @property string $column
 * @property string $old_data
 * @property string $comment
 * @property integer $instrument
 * @property string $datetime
 */
class CallListLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'call_list_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['row_id', 'user_id', 'column', 'instrument'], 'required'],
            [['row_id', 'user_id', 'instrument'], 'integer'],
            [['datetime', 'old_data'], 'safe'],
            [['column', 'comment'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'row_id' => Yii::t('app', 'Row ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'column' => Yii::t('app', 'Column'),
            'old_data' => Yii::t('app', 'Old Data'),
            'comment' => Yii::t('app', 'Comment'),
            'instrument' => Yii::t('app', 'Instrument'),
            'datetime' => Yii::t('app', 'Datetime'),
        ];
    }
}
