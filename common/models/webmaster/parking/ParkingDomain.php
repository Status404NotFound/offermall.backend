<?php

namespace common\models\webmaster\parking;

use Yii;
use common\models\BaseModel;
use common\models\flow\Flow;
use common\modules\user\models\tables\User;
use common\models\geo\Geo;

/**
 * This is the model class for table "{{%parking_domain}}".
 *
 * @property int $domain_id
 * @property string $domain_name
 * @property int $flow_id
 * @property int $wm_id
 * @property int $geo_id
 * @property string $created_at
 * @property string $updated_at
 * @property int $created_by
 * @property int $updated_by
 * @property int $active
 * @property int $is_deleted
 *
 * @property Flow $flow
 * @property User $wm
 * @property Geo $geo
 */
class ParkingDomain extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%parking_domain}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['domain_name', 'flow_id', 'wm_id'], 'required'],
            ['domain_name', 'unique', 'targetAttribute' => ['domain_name', 'wm_id', 'flow_id'], 'when' => function ($model) {
                return $model->is_deleted == 0;
            }],
//            ['domain_name', 'unique', 'when' => function ($model) {
//                return $model->is_deleted == 0;
//            }],
            [['flow_id', 'wm_id', 'geo_id', 'created_by', 'updated_by', 'active', 'is_deleted'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['domain_name'], 'string', 'max' => 255],
            [['created_by'], 'exist', 'skipOnError' => false, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => false, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['flow_id'], 'exist', 'skipOnError' => true, 'targetClass' => Flow::className(), 'targetAttribute' => ['flow_id' => 'flow_id']],
            [['wm_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['wm_id' => 'id']],
            [['geo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Geo::className(), 'targetAttribute' => ['geo_id' => 'geo_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'domain_id' => Yii::t('app', 'Domain ID'),
            'domain_name' => Yii::t('app', 'Domain Name'),
            'flow_id' => Yii::t('app', 'Flow ID'),
            'wm_id' => Yii::t('app', 'Wm ID'),
            'geo_id' => Yii::t('app', 'Geo ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'active' => Yii::t('app', 'Active'),
            'is_deleted' => Yii::t('app', 'Is Deleted'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFlow()
    {
        return $this->hasOne(Flow::className(), ['flow_id' => 'flow_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWm()
    {
        return $this->hasOne(User::className(), ['id' => 'wm_id']);
    }

    public function getGeo()
    {
        return $this->hasOne(Geo::className(), ['geo_id' => 'geo_id']);
    }

    /**
     * @inheritdoc
     * @return ParkingDomainQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ParkingDomainQuery(get_called_class());
    }
}
