<?php
namespace common\services\notify;

use common\services\offer\logic\SaveAdvertNotify;

class AdvertNotifyService
{
    /**
     * @var array
     */
    public $errors = [];

    /**
     * @param $offer_id
     * @param $data
     * @return bool
     * @throws \common\services\offer\OfferNotFoundException
     * @throws \yii\db\Exception
     */
    public function saveAdvertNotify($offer_id, $data)
    {
        $cmd = new SaveAdvertNotify($offer_id, $data);
        if ($cmd->execute() !== true) {
            $this->errors['SaveAdvertNotify'] = $cmd->getErrors();
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}