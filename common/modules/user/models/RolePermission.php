<?php

namespace common\modules\user\models;

use Yii;

/**
 * This is the model class for table "role_permission".
 *
 * @property integer $role_permission_id
 * @property integer $role_id
 * @property integer $permission_id
 * @property integer $is_active
 */
class RolePermission extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'role_permission';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['role_id', 'permission_id', 'is_active'], 'required'],
            [['role_id', 'permission_id', 'is_active'], 'integer'],
            [['role_id', 'permission_id'], 'unique', 'targetAttribute' => ['role_id', 'permission_id'], 'message' => 'The combination of Role ID and Permission ID has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'role_permission_id' => 'Role Permission ID',
            'role_id' => 'Role ID',
            'permission_id' => 'Permission ID',
            'is_active' => 'Is Active',
        ];
    }
}
