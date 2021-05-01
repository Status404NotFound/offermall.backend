<?php

namespace common\models\geo;

use common\models\finance\Currency;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "countries".
 *
 * @property integer $id
 * @property string $country_code
 * @property string $country_name
 */
class Countries extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'countries';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['country_code', 'country_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'country_code' => 'Country Code',
            'country_name' => 'Country Name',
        ];
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['country_id' => 'id']);
    }
}
