<?php

namespace common\models\order;

use Yii;
use yii\db\ActiveRecord;
use common\modules\user\models\tables\User;

/**
 * This is the model class for table "{{%tax_invoice_print}}".
 *
 * @property int $id
 * @property int $order_id
 * @property string $tax_invoice
 * @property int $printed_by
 * @property string $datetime
 *
 * @property Order $order
 * @property User $printedBy
 */
class TaxInvoicePrint extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tax_invoice_print}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'printed_by'], 'integer'],
            [['tax_invoice', 'printed_by'], 'required'],
            [['datetime'], 'safe'],
            [['tax_invoice'], 'string', 'max' => 255],
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
            'id' => Yii::t('app', 'ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'tax_invoice' => Yii::t('app', 'Tax Invoice'),
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
