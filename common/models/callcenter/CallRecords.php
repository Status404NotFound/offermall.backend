<?php

namespace common\models\callcenter;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "call_records".
 *
 * @property string $id
 * @property string $call_id
 * @property string $order_id
 * @property string $uniqueid
 * @property string $path
 * @property string $add_date
 */
class CallRecords extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'call_records';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['call_id', 'order_id', 'uniqueid', 'path'], 'required'],
            [['call_id', 'add_date'], 'integer'],
            [['order_id'], 'string', 'max' => 32],
            [['uniqueid'], 'string', 'max' => 24],
            [['path'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'call_id' => Yii::t('app', 'Call ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'uniqueid' => Yii::t('app', 'Uniqueid'),
            'path' => Yii::t('app', 'Path'),
            'add_date' => Yii::t('app', 'Add Date'),
        ];
    }

    /**
     * @param string $order_id
     * @param string $call_id
     * @return mixed
     */
    public static function getCallRecord(string $order_id, string $call_id)
    {
        $query = self::find()
            ->select(['path'])
            ->where(['order_id' => $order_id])
            ->andWhere(['call_id' => $call_id])
            ->asArray()
            ->one();

        return ArrayHelper::getValue($query, 'path');
    }
}
