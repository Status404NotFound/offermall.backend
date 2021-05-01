<?php
$cc_api = 'http://62.80.191.118:800/api/';
//$cloud_cc_api = 'http://46.4.70.140:88/api/';
$cloud_cc_api = 'http://46.4.70.140:88/api/';
$cc_api2 = 'http://5.32.124.26:82/api/';
$cc_api_KE = 'http://172.20.0.7/api/';
$asterisk_records = 'http://records.crmka.net';
//$asterisk_records = 'http://records.advertfish.com';
return [
    'frontendURL' => 'http://localhost:4200/',
    'supportEmail' => 'admin@example.com',
    'user.passwordResetTokenExpire' => 3600,
    'adminEmail' => 'admin@example.com',
    'callCenterApi' => $cc_api,
    'callCenterApiKE' => $cc_api_KE,
    'callCenterApi2' => $cc_api2,
    'cloudCallCenterApi' => $cloud_cc_api,
    'callCenterRecords' => $asterisk_records,
];
