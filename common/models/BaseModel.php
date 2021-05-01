<?php

namespace common\models;

use Yii;
use common\helpers\FishHelper;
use common\models\order\Order;
use common\services\log\logs\LogsService;
use common\models\finance\ClosedFinancialPeriods;
use yii\base\Exception;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

class BaseModel extends ActiveRecord
{
    /**
     * @return array
     */
    public function behaviors()
    {
        if (isset(Yii::$app->session) && isset(Yii::$app->user->identity)) {
            $user_id = Yii::$app->user->identity->getId();
        } else {
            $user_id = null;
        }
//        $user_id = (isset(Yii::$app->user->identity)) ? Yii::$app->user->identity->getId() : null;
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => $user_id,
            ],
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws Exception
     */
    public function beforeSave($insert)
    {
        $enable_period = Yii::$app->request->post('enable_closed_period') ? Yii::$app->request->post('enable_closed_period') : 0;

        if ($enable_period === 0) {
            $this->validateChangingDate();
        }

        // TODO: Create Factory
        $oldModel = self::getModelByPk();
        (new LogsService())->logModel($this, $oldModel, false);
//        if (isset($this->img))
//        FishHelper::debug($this->img, 1, 1);

        return parent::beforeSave($insert);
    }

    /**
     * @return null|static
     */
    public function getModelByPk()
    {
        $pkName = (string)$this::primaryKey()[0];
        $pkValue = (int)$this->$pkName;
        return self::findOne([$pkName => $pkValue]);
    }

    /**
     * @throws Exception
     */
    private final function validateChangingDate()
    {
        $close_period = ClosedFinancialPeriods::find()->select('date')->orderBy(['period_id' => SORT_DESC])->one();
        $date_to = $close_period->date;

        $modelName = $this->tableName();

        if (($modelName == 'order_sku')
            && isset($this->order_id)
        ) {
            $order = Order::find()->select('created_at')->where(['order_id' => $this->order_id])->one();
            if (isset($order->created_at) && $order->created_at < $date_to) {
                throw new Exception('This Conversion Can\'t be change.');
            }
        } elseif ($modelName == 'customer') {
            $order = Order::find()->select('created_at')->where(['customer_id' => $this->customer_id])->one();
            if (isset($order->created_at) && $order->created_at < $date_to) {
                throw new Exception('This Conversion Can\'t be change.');
            }
        } elseif ($modelName == 'order') {
            if (isset($this->created_at) && $this->created_at < $date_to) {
                throw new Exception('This Conversion Can\'t be change.');
            }
        }
    }
}