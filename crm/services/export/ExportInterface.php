<?php

namespace crm\services\export;

interface ExportInterface
{
    public function compareData($filters = null): array;
}