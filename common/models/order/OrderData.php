<?php

namespace common\models\order;

use common\models\BaseModel;
use common\models\log\orderInfo\OrderInfoInstrument;
use common\models\offer\Offer;
use common\modules\user\models\tables\User;
use common\services\log\orderInfo\OrderInfoLogService;
use Yii;

/**
 * This is the model class for table "order_data".
 *
 * @property integer $order_data_id
 * @property integer $order_id
 * @property integer $order_hash
 * @property integer $offer_id
 * @property integer $owner_id
 * @property integer $wm_id
 * @property string $fields
 * @property string $view_time
 * @property string $view_hash
 * @property string $referrer
 * @property string $sub_id_1
 * @property string $sub_id_2
 * @property string $sub_id_3
 * @property string $sub_id_4
 * @property string $sub_id_5
 * @property string $declaration
 * @property integer $updated_by
 * @property string $updated_at
 * @property string $comment
 *
 * @property Offer $offer
 * @property Order $order
 * @property User $owner
 * @property User $wm
 */
class OrderData extends BaseModel
{
    public $created_at = null;
    public $created_by = null;
    public $instrument = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_data';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'owner_id', 'wm_id', 'order_hash', 'offer_id', 'updated_by'], 'integer'],
            [['updated_at'], 'safe'],
            [['fields', 'comment'], 'string'],
            [['view_time', 'view_hash', 'referrer', 'sub_id_1', 'sub_id_2', 'sub_id_3', 'sub_id_4', 'sub_id_5', 'declaration'], 'string', 'max' => 255],
            [['offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Offer::className(), 'targetAttribute' => ['offer_id' => 'offer_id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'order_id']],
            [['owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['owner_id' => 'id']],
            [['wm_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['wm_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_data_id' => Yii::t('app', 'Order Data ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'order_hash' => Yii::t('app', 'Order Hash'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'owner_id' => Yii::t('app', 'Owner ID'),
            'wm_id' => Yii::t('app', 'Wm ID'),
            'fields' => Yii::t('app', 'Fields'),
            'view_time' => Yii::t('app', 'View Time'),
            'view_hash' => Yii::t('app', 'View Hash'),
            'referrer' => Yii::t('app', 'Referrer'),
            'sub_id_1' => Yii::t('app', 'Sub Id 1'),
            'sub_id_2' => Yii::t('app', 'Sub Id 2'),
            'sub_id_3' => Yii::t('app', 'Sub Id 3'),
            'sub_id_4' => Yii::t('app', 'Sub Id 4'),
            'sub_id_5' => Yii::t('app', 'Sub Id 5'),
            'declaration' => Yii::t('app', 'Declaration'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'comment' => Yii::t('app', 'Comment'),
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $oldModel = self::getModelByPk();
        if (isset($this->instrument) && $this->instrument == true)
            (new OrderInfoLogService())->logModel($this, $oldModel, false);
        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['offer_id' => 'offer_id']);
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
    public function getOwner()
    {
        return $this->hasOne(User::className(), ['id' => 'owner_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWm()
    {
        return $this->hasOne(User::className(), ['id' => 'wm_id']);
    }

    /**
     * @return mixed
     */
    public function getInstrument()
    {
        return $this->instrument;
    }

    /**
     * @param $instrument
     * @return bool
     */
    public function setInstrument($instrument)
    {
        if (OrderInfoInstrument::findInstrument($instrument) && $this->instrument = $instrument) return true;
        return false;
    }
}
