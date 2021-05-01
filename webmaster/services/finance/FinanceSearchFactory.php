<?php

namespace webmaster\services\finance;

use webmaster\services\finance\logic\Finance;
use webmaster\services\finance\logic\Hold;

/**
 * Class FinanceFactory
 * @package webmaster\services\finance
 */
class FinanceSearchFactory
{
    private const PAGES = [
        'finance' => Finance::class,
        'hold' => Hold::class
    ];

    /**
     * @param $page
     * @return mixed
     */
    public static function createFinanceSearch($page)
    {
        $class = self::PAGES[$page];
        if (!$class)
            throw new \InvalidArgumentException("Page type $page not found.");
        return new $class;
    }
}