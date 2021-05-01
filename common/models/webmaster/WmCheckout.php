<?php

namespace common\models\webmaster;

use Yii;
use common\models\BaseModel;
use common\modules\user\models\tables\User;

/**
 * This is the model class for table "{{%wm_checkout}}".
 *
 * @property integer $wm_checkout_id
 * @property integer $wm_id
 * @property double $amount
 * @property integer $status
 * @property string $comment
 * @property string $created_at
 *
 * @property User $wm
 */
class WmCheckout extends BaseModel
{
    public $created_by = false;
    public $updated_by = false;

    const IN_PROCESSING = 0;
    const PAID_OUT = 1;
    const REJECTED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wm_checkout}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['wm_id', 'amount'], 'required'],
            [['wm_id', 'status'], 'integer'],
            [['wm_username'], 'string'],
            [['amount'], 'number'],
            [['comment'], 'string', 'max' => 255],
            [['created_at'], 'safe'],
            [['updated_at'], 'safe'],
            [['wm_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['wm_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'wm_checkout_id' => Yii::t('app', 'Wm Checkout ID'),
            'wm_id' => Yii::t('app', 'Wm ID'),
            'wm_username' => Yii::t('app', 'Wm username'),
            'amount' => Yii::t('app', 'Amount'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWm()
    {
        return $this->hasOne(User::className(), ['id' => 'wm_id']);
    }

    /**
     * @param null $status_id
     * @return array|mixed
     */
    public static function statusLabels($status_id = null)
    {
        $statuses = [
            self::IN_PROCESSING => Yii::t('app', 'In progress'),
            self::PAID_OUT => Yii::t('app', 'Paid out'),
            self::REJECTED => Yii::t('app', 'Rejected'),
        ];

        return isset($status_id) ? $statuses[$status_id] : $statuses;
    }

    /**
     * @param null $status_id
     * @return array|mixed
     */
    public static function statusList($status_id = null)
    {
        $statuses = [
            ['status_id' => self::IN_PROCESSING, 'status_name' => Yii::t('app', 'In progress')],
            ['status_id' => self::PAID_OUT, 'status_name' => Yii::t('app', 'Paid out')],
            ['status_id' => self::REJECTED, 'status_name' => Yii::t('app', 'Rejected')],
        ];

        return isset($status_id) ? $statuses[$status_id] : $statuses;
    }

    public static function getById($id){
        return self::findOne($id);
    }
}