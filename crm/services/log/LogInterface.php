<?php

namespace crm\services\log;

use yii\db\ActiveRecord;

interface LogInterface
{
    public function compareData(ActiveRecord $model, $primaryKey = null);

    public function saveLog();

    public function setComment($comment);

    public function setInstrument($instrument);
}