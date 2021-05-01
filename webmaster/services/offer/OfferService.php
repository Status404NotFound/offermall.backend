<?php

namespace webmaster\services\offer;

/**
 * Class OfferService
 * @package webmaster\services\offer
 */
class OfferService
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param array $filters
     * @param null $pagination
     * @param null $sort_order
     * @param null $sort_field
     * @return array
     */
    public function findOffers($filters = [], $pagination = null, $sort_order = null, $sort_field = null)
    {
        $cmd = new OfferSearch();
        $result = $cmd->getOffersList($filters, $pagination, $sort_order, $sort_field);
        return $result;
    }

    /**
     * @param $offer_id
     * @param $data
     * @return bool
     * @throws \common\services\offer\OfferNotFoundException
     * @throws \yii\db\Exception
     */
    public function saveOfferData($offer_id, $data)
    {
        $cmd = new OfferDataSave($offer_id, $data);
        if ($cmd->execute() !== true) {
            $this->errors['SaveOfferData'] = $cmd->getErrors();
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