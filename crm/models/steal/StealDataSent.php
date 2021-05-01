<?php

namespace crm\models\steal;

use Yii;

/**
 * This is the model class for table "steal_data_sent".
 *
 * @property integer $site_id
 * @property string $site
 * @property string $date_sent
 * @property integer $status
 */
class StealDataSent extends \yii\db\ActiveRecord
{
    const STATUS_NOT_VIEW = 0;
    const STATUS_VIEW = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'steal_data_sent';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['site', 'status'], 'required'],
            [['date_sent'], 'safe'],
            [['status'], 'integer'],
            [['site'], 'string', 'max' => 255],
            ['status', 'default', 'value' => self::STATUS_NOT_VIEW],
            ['status', 'in', 'range' => [self::STATUS_VIEW, self::STATUS_NOT_VIEW]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'site_id' => Yii::t('app', 'Site ID'),
            'site' => Yii::t('app', 'Site'),
            'date_sent' => Yii::t('app', 'Date Sent'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @param $status_id
     * @return array|mixed
     */
    static public function statuses($status_id)
    {
        $statuses = [
            self::STATUS_NOT_VIEW => Yii::t('app', 'No'),
            self::STATUS_VIEW => Yii::t('app', 'Yes'),
        ];

        return isset($status_id) ? $statuses[$status_id] : $statuses;
    }
}
