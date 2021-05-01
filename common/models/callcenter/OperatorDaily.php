<?php
/**
 * Created by PhpStorm.
 * User: evild
 * Date: 5/15/18
 * Time: 2:04 PM
 */

namespace common\models\callcenter;

use Yii;

/**
 * This is the model class for table "operator_daily".
 *
 * @property integer $id
 * @property integer $operator_id
 * @property string $date
 * @property integer $active_time
 * @property integer $inactive_time
 */
class OperatorDaily extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'operator_daily';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'operator_id', 'active_time', 'inactive_time'], 'integer'],
            [['date'], 'safe'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'operator_id' => Yii::t('app', 'Operator ID'),
            'date' => Yii::t('app', 'Date'),
            'active_time' => Yii::t('app', 'Active Time'),
            'inactive_time' => Yii::t('app', 'Inactive Time'),
        ];
    }
}