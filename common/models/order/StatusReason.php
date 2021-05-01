<?php

namespace common\models\order;

class StatusReason extends OrderStatus
{
    public static function getStatusReasons($order_status)
    {
        return self::statusReasons()[$order_status] ?? null;
    }

    public static function getReason($order_status, $reason)
    {
        return self::statusReasons()[$order_status][$reason] ?? null;
    }

    public static function getStatusesId()
    {
        $statuses = self::statusReasons();
        return array_keys($statuses);
    }

    public static function getIndexedReasons($order_status)
    {
        $result = [];
        $status_reasons = self::getStatusReasons($order_status);
        foreach ($status_reasons as $key => $val) {
            $result[] = ['reason_id' => $key, 'description' => $val];
        }
        return $result;
    }

    public static function statusReasons()
    {
        return [
            parent::BACK_TO_PENDING => [
                0 => 'Back to pending by Admin',
                1 => 'I can not find any duplicate orders for this offer',
                2 => 'This is not reason for not valid',
                3 => 'I correct Phone number',
                4 => 'I add alternative phone number for this client',
                5 => 'I can not find mistakes in the mobile phone number',
                6 => 'More than 30 days have passed since the last valid order for this offer',
                7 => 'Black list',
            ],
            parent::NOT_VALID => [
                0 => 'Not Valid by Admin',
                1 => 'Incomplete Mobile Number',
                2 => 'Duplicate order',
                3 => 'Product is out of stock',
                4 => 'Not Supported Region',
                5 => 'Test Conversion or Offer',
                6 => 'Weird details',
                7 => 'Made order to complain',
                8 => 'International Mobile Number',
                9 => 'Error in the Mobile Phone Number',
                10 => 'Black list',
                20 => 'Wrong Geo',
                30 => 'Not Valid by Partner',
            ],
            parent::NOT_VALID_CHECKED => [
                0 => 'Not Valid by Admin',
                1 => 'Incomplete Mobile Number',
                2 => 'Duplicate order',
                3 => 'Product is out of stock',
                4 => 'Not Supported Region',
                5 => 'Test Conversion or Offer',
                6 => 'Weird details',
                7 => 'Made order to complain',
                8 => 'International Mobile Number',
                9 => 'Error in the Mobile Phone Number',
                10 => 'Black list',
                20 => 'Wrong Geo',
                30 => 'Not Valid by Partner',
            ],
            parent::CANCELED => [
                0 => 'Canceled by Admin',
                1 => 'Error in the phone number',
                2 => 'I did not order',
                3 => 'Duplicate order',
                4 => 'Too expensive',
                5 => 'Ordered elsewhere',
                6 => 'Order to mistress but wife took the phone',
                7 => 'A subscriber can not receive the call at the moment',
                8 => 'Undefined language',
                9 => 'Product is out of stock',
                10 => 'Customer will order late',
                11 => 'Test Conversion or Offer',
                12 => 'Unhappy with delivery charge',
                13 => 'Children\'s joke',
                14 => 'Consultation',
                15 => 'Not Supported Region',
                16 => 'Customer is not interested anymore',
                17 => 'Weird details',
                18 => 'The subscriber is out of network coverage',
                19 => 'Low quality',
                20 => 'Customer is out of country',
                21 => 'Black list',
            ],
            parent::REJECTED => [
                0 => 'Rejected by Admin', //nv
                1 => 'Customer is out of country', //v
                2 => 'Error in the phone number', //nv
                3 => 'I did not order', //v
                4 => 'Duplicate order', //nv
                5 => 'Too expensive', //v
                6 => 'Ordered elsewhere', //v
                7 => 'Order to mistress but wife took the phone', //v
                8 => 'A subscriber can not receive the call at the moment', //nv
                9 => 'Undefined language', //v
                10 => 'Product is out of stock', //nv
                11 => 'Customer will order late', //v
                12 => 'Not Supported Region', //nv
                13 => 'Test Conversion or Offer', //nv
                14 => 'Unhappy with delivery charge', //v
                15 => 'Children\'s joke', //v
                16 => 'Consultation', //v
                17 => 'Customer is not interested anymore', //v
                18 => 'Weird details', //nv
                19 => 'The subscriber is out of network coverage', //nv
                20 => 'Wrong Geo', //nv
                21 => 'No WM', //nv
                22 => 'Not correct details', //nv
                23 => 'NA for 4 days', //nv
                24 => 'Low quality', //v
                25 => 'Out of money', //v
                26 => 'Black list', //nv
            ],
        ];
    }
}