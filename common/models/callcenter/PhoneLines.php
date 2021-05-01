<?php

namespace common\models\callcenter;

use Yii;

/**
 * This is the model class for table "phone_lines".
 *
 * @property integer $id
 * @property integer $owner_id
 * @property integer $line
 * @property integer $country_id
 * @property string $asterisk_id
 * @property string $created_at
 * @property string $updated_at
 */
class PhoneLines extends \yii\db\ActiveRecord
{
    public $owner_name;
    public $country_name;
    public $country_code;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'phone_lines';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['owner_id', 'line', 'country_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['asterisk_id'], 'string', 'max' => 255],
            [['owner_id', 'line'], 'unique', 'targetAttribute' => ['owner_id', 'line'], 'message' => 'The combination of Owner ID and Line has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'owner_id' => Yii::t('app', 'Owner ID'),
            'line' => Yii::t('app', 'Line'),
            'country_id' => Yii::t('app', 'Country ID'),
            'asterisk_id' => Yii::t('app', 'Asterisk ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
