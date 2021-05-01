<?php

namespace common\models\callcenter;

use Yii;
use common\modules\user\models\tables\User;

/**
 * This is the model class for table "operator_fine".
 *
 * @property integer $operator_fine_id
 * @property integer $operator_id
 * @property integer $status_id
 * @property string $created_at
 *
 * @property User $operator
 */
class OperatorFine extends \yii\db\ActiveRecord
{
    const STATUS_PENDIG = 1;
    const STATUS_APPROVE = 2;
    const STATUS_REJECT = 3;
    const STATUS_PAID = 4;

    public static function tableName()
    {
        return 'operator_fine';
    }

    public function rules()
    {
        return [
            [['operator_id', 'status_id'], 'required'],
            [['operator_id', 'status_id'], 'integer'],
            [['created_at'], 'safe'],
            [['operator_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['operator_id' => 'id']],
        ];
    }


    public static function getStatus()
    {
        return [
            self::STATUS_PENDIG => 'Pending',
            self::STATUS_APPROVE => 'Approve',
            self::STATUS_REJECT => 'Reject',
            self::STATUS_PAID => 'Paid',
        ];
    }

    public function status()
    {
        return self::getStatus()[$this->status];
    }

    public function getOperator()
    {
        return $this->hasOne(User::className(), ['id' => 'operator_id']);
    }
}
