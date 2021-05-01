<?php

namespace common\models\callcenter;

use Yii;

/**
 * This is the model class for table "operator_activity".
 *
 * @property integer $operator_activity_id
 * @property integer $operator_id
 * @property integer $operator_status
 * @property integer $action
 * @property integer $process
 * @property integer $is_approved
 * @property string $datetime
 */
class OperatorActivity extends \yii\db\ActiveRecord
{
    const ACTION_CLOSE_WINDOW = 1;
    const ACTION_OPEN_WINDOW = 2;
    const ACTION_NOT_ACTIVE_MOUSE = 3;
    const ACTION_START_OF_SHIFT = 4;
    const ACTION_END_OF_SHIFT = 5;
    const ACTION_BREAK = 6;
    const ACTION_CARD = 7;

    const PROCESS_AUTO = 1;
    const PROCESS_MANUAL = 2;

    public $username;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'operator_activity';
    }

    public static function getActionList()
    {
        return [
            self::ACTION_CLOSE_WINDOW => 'Close window',
            self::ACTION_OPEN_WINDOW => 'Open window',
            self::ACTION_NOT_ACTIVE_MOUSE => 'Not active mouse',
            self::ACTION_START_OF_SHIFT => 'Start of shift',
            self::ACTION_END_OF_SHIFT => 'End of shift',
            self::ACTION_BREAK => 'Break',
        ];
    }

    public static function getProcessList()
    {
        return [
            self::PROCESS_AUTO => 'Auto',
            self::PROCESS_MANUAL => 'Manual',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['operator_id', 'operator_status', 'datetime', 'action', 'process', 'is_approved'], 'required'],
            [['operator_id', 'operator_status', 'action', 'process'], 'integer'],
            [['is_approved'], 'boolean'],
            [['datetime'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'operator_activity_id' => 'ID',
            'operator_id' => 'Operator ID',
            'operator_status' => 'Operator Status',
            'datetime' => 'Status Time',
        ];
    }
}
