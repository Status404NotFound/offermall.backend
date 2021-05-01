<?php

namespace tds\modules\genform\tables;

use Yii;

/**
 * This is the model class for table "{{%genform}}".
 *
 * @property integer $id
 * @property integer $id_user
 * @property integer $id_site
 * @property string $title
 * @property string $fields
 * @property string $design
 * @property string $modules
 */
class Genform extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%genform}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_user', 'id_site'], 'required'],
            [['id_user', 'id_site'], 'integer'],
            [['fields', 'design', 'modules'], 'string'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'id_user' => Yii::t('app', 'Id User'),
            'id_site' => Yii::t('app', 'Id Site'),
            'title' => Yii::t('app', 'Title'),
            'fields' => Yii::t('app', 'Fields'),
            'design' => Yii::t('app', 'Design'),
            'modules' => Yii::t('app', 'Modules'),
        ];
    }
}
