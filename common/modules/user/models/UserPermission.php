<?php

namespace common\modules\user\models;

use Yii;

/**
 * This is the model class for table "user_permission".
 *
 * @property integer $user_permission_id
 * @property integer $user_id
 * @property integer $permission_id
 * @property integer $is_active
 */
class UserPermission extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_permission';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'permission_id', 'is_active'], 'required'],
            [['user_id', 'permission_id', 'is_active'], 'integer'],
            [['user_id', 'permission_id'], 'unique', 'targetAttribute' => ['user_id', 'permission_id'], 'message' => 'The combination of User ID and Permission ID has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_permission_id' => 'User Permission ID',
            'user_id' => 'User ID',
            'permission_id' => 'Permission ID',
            'is_active' => 'Is Active',
        ];
    }
}
