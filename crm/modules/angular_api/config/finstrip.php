<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/finstrip',
    'pluralize' => false,
    'tokens' => [
        '{advert_id}' => '<advert_id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS' => 'options',

        'POST financial-period/list' => 'financial-period-list',
        'OPTIONS financial-period/list' => 'options',

        'GET financial-period/check' => 'financial-period-check',
        'OPTIONS financial-period/check' => 'options',

        'POST financial-period/verify' => 'financial-period-verify',
        'OPTIONS financial-period/verify' => 'options',

        'GET financial-period' => 'financial-period',
        'OPTIONS financial-period' => 'options',

        'POST close-financial-period' => 'close-financial-period',
        'OPTIONS close-financial-period' => 'options',

        /** Changing sub cost */
        'POST day-offer-geo-sub-cost' => 'day-offer-geo-sub-cost',
        'OPTIONS day-offer-geo-sub-cost' => 'options',

        'GET get-known-sub-list' => 'get-known-sub-list',
        'OPTIONS get-known-sub-list' => 'options',

        /**
         * Finstrip Calendar routes
         */
        'OPTIONS calendar' => 'options',
        'OPTIONS month' => 'options',
        'OPTIONS day' => 'options',
        'OPTIONS day-offer' => 'options',
        'OPTIONS day-offer-geo' => 'options',

        'POST calendar' => 'calendar',
        'POST month' => 'month',
        'POST day' => 'day',
        'POST day-offer' => 'day-offer',
        'POST day-offer-geo' => 'day-offer-geo',

        /**
         * Finstrip Offer routes
         */
        'OPTIONS offers' => 'options',
        'OPTIONS offer' => 'options',
        'OPTIONS offer-geo' => 'options',
        'OPTIONS offer-month' => 'options',
        'OPTIONS offer-day' => 'options',

        'POST offers' => 'offers',
        'POST offer' => 'offer',
        'POST offer-geo' => 'offer-geo',
        'POST offer-month' => 'offer-month',
        'POST offer-day' => 'offer-day',

        /**
         * Finstrip summary routes
         */
        'OPTIONS summary-offers' => 'options',
        'OPTIONS summary-offer-sub' => 'options',
        'OPTIONS summary-offer-geo' => 'options',
        'OPTIONS summary-month' => 'options',
        'OPTIONS summary-offer-days' => 'options',

        'POST summary-offers' => 'summary-offers',
        'POST summary-offer-sub' => 'summary-offer-sub',
        'POST summary-offer-geo' => 'summary-offer-geo',
        'POST summary-month' => 'summary-month',
        'POST summary-offer-days' => 'summary-offer-days',
    ],
];