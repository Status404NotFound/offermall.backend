<?php
$cc_api = 'http://172.20.0.4/api/';
//$cc_api = 'http://62.80.191.118:800/api/';
//$cloud_cc_api = 'http://46.4.70.140:88/api/';
$cloud_cc_api = 'http://46.4.119.167:88/api/';
$cc_api_KE = 'http://172.20.0.7/api/';
//$asterisk_records = 'http://records.advertfish.com';
$asterisk_records = 'http://records.crmka.net';
return [
    'adminEmail' => 'admin@example.com',
    'smtpEmail' => 'leadcrmka.net',
    'callCenterApi' => $cc_api,
    'cloudCallCenterApi' => $cloud_cc_api,
    'callCenterApiKE' => $cc_api_KE,
    'callCenterRecords' => $asterisk_records,
];
