<?php

namespace common\services\partner;

use common\services\partner\logic\LpCrm;
use common\services\partner\logic\MyLandCrm;

/**
 * Class PartnerFactory
 * @package crm\services\export
 */
class PartnerFactory
{
    /**
     * @var array
     */
    private static $map = [
        'lp_crm' => LpCrm::class,
        'my_land_crm' => MyLandCrm::class,
    ];

    /**
     * @param $partner_slug
     * @return mixed
     */
    public static function createPartner($partner_slug)
    {
        if (!isset(self::$map[$partner_slug]))
            throw new \InvalidArgumentException("Partner type $partner_slug not found.");

        return new self::$map[$partner_slug];
    }
}
