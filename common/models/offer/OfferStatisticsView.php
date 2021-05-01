<?php

namespace common\models\offer;
use Yii;
use yii\db\ActiveRecord;

class OfferStatisticsView extends ActiveRecord
{
    public static function tableName()
    {
        return 'offer_statistics_view';
    }


    public function rules()
    {
        return [
            [['offer_id', 'geo_id', 'views', 'uniques',
                'pending',
                'back_to_pending',
                'waiting_for_delivery',
                'delivery_in_progress',
                'success_delivery',
                'missed_delivery',
                'canceled',
                'rejected',
                'not_valid',
                'not_valid_checked',
                'not_paid',
                'returned',
                'total',
            ], 'integer'],
            [['offer_name', 'geo_name'], 'string', 'max' => 255],
        ];
    }
}