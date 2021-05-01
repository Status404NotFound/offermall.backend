<?php

namespace common\models\delivery;

use Yii;
use common\models\BaseModel;
use common\modules\user\models\tables\User;
use common\models\geo\Geo;

/**
 * This is the model class for table "{{%user_requisites}}".
 *
 * @property int $requisite_id
 * @property int $user_id
 * @property int $geo_id
 * @property string $description
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Geo $geo
 * @property User $user
 */
class UserRequisites extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_requisites}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'geo_id'], 'required'],
            [['user_id', 'geo_id', 'created_by', 'updated_by'], 'integer'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['geo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Geo::className(), 'targetAttribute' => ['geo_id' => 'geo_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'requisite_id' => Yii::t('app', 'Requisite ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'geo_id' => Yii::t('app', 'Geo ID'),
            'description' => Yii::t('app', 'Description'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeo()
    {
        return $this->hasOne(Geo::className(), ['geo_id' => 'geo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
