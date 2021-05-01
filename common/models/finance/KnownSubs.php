<?php

namespace common\models\finance;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "known_sub_id_1".
 *
 * @property integer $id
 * @property string $name
 * @property string $alias
 */
class KnownSubs extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'known_sub_id_1';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'alias'], 'required'],
            [['name', 'alias'], 'string', 'max' => 255],
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
            'alias' => Yii::t('app', 'Alias'),
        ];
    }
}