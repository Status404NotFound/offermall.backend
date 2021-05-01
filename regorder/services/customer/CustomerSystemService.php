<?php

namespace regorder\services\customer;


use common\services\GeoService;

class CustomerSystemService
{
    private $userAgent;

    public function __construct($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    public function getBrowser()
    {
        $user_agent = $this->userAgent;
        $browser = [
            'opera' => function ($user_agent) {
                return (preg_match('/opera/i', $user_agent)) ? true : false;
            },
            'msie' => function ($user_agent) {
                return (preg_match('/msie/i', $user_agent) && !preg_match('/opera/i', $user_agent)) ? true : false;
            },
            'msie6' => function ($user_agent) {
                return (preg_match('/msie 6/i', $user_agent) && !preg_match('/opera/i', $user_agent)) ? true : false;
            },
            'msie7' => function ($user_agent) {
                return (preg_match('/msie 7/i', $user_agent) && !preg_match('/opera/i', $user_agent)) ? true : false;
            },
            'msie8' => function ($user_agent) {
                return (preg_match('/msie 8/i', $user_agent) && !preg_match('/opera/i', $user_agent)) ? true : false;
            },
            'msie9' => function ($user_agent) {
                return (preg_match('/msie 9/i', $user_agent) && !preg_match('/opera/i', $user_agent)) ? true : false;
            }, 'msie10' => function ($user_agent) {
                return (preg_match('/msie 10/i', $user_agent) && !preg_match('/opera/i', $user_agent)) ? true : false;
            },
            'msie11' => function ($user_agent) {
                return (preg_match('/msie 11/i', $user_agent) && !preg_match('/opera/i', $user_agent)) ? true : false;
            },
            'mozilla' => function ($user_agent) {
                return (preg_match('/firefox/i', $user_agent)) ? true : false;
            },
            'chrome' => function ($user_agent) {
                return (preg_match('/chrome/i', $user_agent)) ? true : false;
            },
            'safari' => function ($user_agent) {
                return (!preg_match('/chrome/i', $user_agent) && preg_match('/webkit|safari|khtml/i', $user_agent)) ? true : false;
            },
            'iphone' => function ($user_agent) {
                return (preg_match('/iphone/i', $user_agent)) ? true : false;
            },
            'ipod' => function ($user_agent) {
                return (preg_match('/ipod/i', $user_agent)) ? true : false;
            },
            'iphone4' => function ($user_agent) {
                return (preg_match('/iphone.*OS 4/i', $user_agent)) ? true : false;
            },
            'ipod4' => function ($user_agent) {
                return (preg_match('/ipod.*OS 4/i', $user_agent)) ? true : false;
            },
            'ipad' => function ($user_agent) {
                return (preg_match('/ipad/i', $user_agent)) ? true : false;
            },
            'ios' => function ($user_agent) {
                return (preg_match('/ipad|ipod|iphone/i', $user_agent)) ? true : false;
            },
            'android' => function ($user_agent) {
                return (preg_match('/android/i', $user_agent)) ? true : false;
            },
            'bada' => function ($user_agent) {
                return (preg_match('/bada/i', $user_agent)) ? true : false;
            },
            'mobile' => function ($user_agent) {
                return (preg_match('/iphone|ipod|ipad|opera mini|opera mobi|iemobile/i', $user_agent)) ? true : false;
            },
            'msie_mobile' => function ($user_agent) {
                return (preg_match('/iemobile/i', $user_agent)) ? true : false;
            },
            'safari_mobile' => function ($user_agent) {
                return (preg_match('/iphone|ipod|ipad/i', $user_agent)) ? true : false;
            },
            'opera_mobile' => function ($user_agent) {
                return (preg_match('/opera mini|opera mobi/i', $user_agent)) ? true : false;
            },
            'opera_mini' => function ($user_agent) {
                return (preg_match('/opera mini/i', $user_agent)) ? true : false;
            },
            'mac' => function ($user_agent) {
                return (preg_match('/mac/i', $user_agent)) ? true : false;
            },
            'webkit' => function ($user_agent) {
                return (preg_match('/webkit/i', $user_agent)) ? true : false;
            },
            'version' => function ($user_agent) {
                return (preg_match('/.+(?:me|ox|on|rv|it|era|ie)[\/: ]([\d.]+)/', $user_agent, $matches)) ? $matches[1] : 0;
            },
            'android_version' => function ($user_agent) {
                $start = strpos($user_agent, "Android") + 8;
                $end = strpos(substr($user_agent, $start), ' ');
                return $start !== 8 ? substr($user_agent, $start, $end) : 0;
            },
        ];

        $results = '';
        foreach ($browser as $key => $value) {
            if (($val = $value($user_agent))) {
                if ($key == 'version' || $key == 'android_version') {
                    $results .= $key . '_' . $val;
                } else {
                    $results .= $key . ' ';
                }
            }
        }
        return $results;
    }

    public function getOS()
    {

        $os_platform = "Unknown OS Platform";
        $os_array = [
            '/windows nt 10/i' => 'Windows 10',
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        ];

        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $this->userAgent)) {
                $os_platform = $value;
            }
        }
        return $os_platform;
    }
}