<?php

namespace tds\modules\genform\tables;

use Yii;

/**
 * This is the model class for table "{{%gen_form_table}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $hash
 * @property integer $user_id
 * @property integer $offer_id
 * @property string $theme
 * @property string $extensions
 * @property string $hidden_conf
 * @property string $pages_conf
 */
class GenFormTable extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%gen_form_table}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'hash', 'user_id', 'theme'], 'required'],
            [['user_id', 'offer_id'], 'integer'],
            [['extensions', 'hidden_conf', 'pages_conf'], 'string'],
            [['name', 'hash', 'theme'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'hash' => Yii::t('app', 'Hash'),
            'user_id' => Yii::t('app', 'User ID'),
            'offer_id' => Yii::t('app', 'Offer Id'),
            'theme' => Yii::t('app', 'Theme'),
            'extensions' => Yii::t('app', 'Extensions'),
            'hidden_conf' => Yii::t('app', 'Hidden Conf'),
            'pages_conf' => Yii::t('app', 'Pages Conf'),
        ];
    }
}
