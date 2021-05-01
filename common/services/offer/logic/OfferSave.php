<?php

namespace common\services\offer\logic;

class OfferSave
{
    public $offer;

    public function execute()
    {
        // for example - do something
//        $this->offer->setDefaultCategory();
        // .....

        // TODO: WHAT will be with skus if changes product_id

        $this->offer->save();

        return true;
    }
}