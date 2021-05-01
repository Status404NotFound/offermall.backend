<?php

namespace webmaster\services\finance;

/**
 * Interface FinanceSearchInterface
 * @package webmaster\services\finance
 */
interface FinanceSearchInterface
{
    /**
     * @param array $filters
     * @param null $pagination
     * @param null $sort_order
     * @param null $sort_field
     * @return mixed
     */
    public function searchLeads($filters = [], $pagination = null, $sort_order = null, $sort_field = null);
}