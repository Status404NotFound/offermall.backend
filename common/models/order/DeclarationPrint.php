<?php

namespace common\models\order;

use common\modules\user\models\tables\User;
use yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "declaration_print".
 *
 * @property integer $transfer_id
 * @property integer $order_id
 * @property string $declaration
 * @property integer $printed_by
 * @property string $datetime
 *
 * @property Order $order
 * @property User $printedBy
 */
class DeclarationPrint extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'declaration_print';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'printed_by'], 'integer'],
            [['declaration', 'printed_by'], 'required'],
//            [['datetime'], 'safe'],
            [['declaration'], 'string', 'max' => 255],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'order_id']],
            [['printed_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['printed_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'transfer_id' => Yii::t('app', 'Transfer ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'declaration' => Yii::t('app', 'Declaration'),
            'printed_by' => Yii::t('app', 'Printed By'),
            'datetime' => Yii::t('app', 'Datetime'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['order_id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrintedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'printed_by']);
    }
}