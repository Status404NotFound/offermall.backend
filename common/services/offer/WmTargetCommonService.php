<?php

namespace common\services\offer;

use common\services\offer\logic\SaveWmTargets;

class WmTargetCommonService
{
    public $errors = [];

    public function saveWmTargets($offer_id, $targets)
    {
        $cmd = new SaveWmTargets($offer_id, $targets);
        if ($cmd->execute() !== true) {
            $this->errors['SaveWmTargets'] = $cmd->getErrors();
            return false;
        }
        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}