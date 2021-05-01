<?php

namespace crm\services\order\search;

interface OrderSearchInterface
{
    public function getOrders($filters = null, $pagination = null, $sortOrder = SORT_ASC): array;
}