<?php

namespace crm\services\targets;

use common\helpers\FishHelper;
use common\services\offer\AdvertTargetCommonService;
use crm\services\targets\logic\AdvertTargetDataProvider;
use Yii;

/**
 * Class AdvertTargetService
 * @package crm\services\targets
 */
class AdvertTargetService extends AdvertTargetCommonService
{
    public $errors = [];

    public function getAdvertTargetData($offer_id, $page)
    {
        $cmd = new AdvertTargetDataProvider($offer_id, $page);
        $result = $cmd->compareData();
        return ($result === false) ? $cmd->getErrors() : $result;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}