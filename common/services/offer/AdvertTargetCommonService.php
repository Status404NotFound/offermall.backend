<?php

namespace common\services\offer;

use common\services\offer\logic\SaveAdvertTargets;

class AdvertTargetCommonService
{
    public $errors = [];

    public function saveAdvertTargets($offer_id, $targets)
    {
        $command = new SaveAdvertTargets($offer_id, $targets);
        if ($command->execute() !== true) {
            $this->errors['SaveAdvertTargets'] = $command->getErrors();
            return false;
        }
        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}