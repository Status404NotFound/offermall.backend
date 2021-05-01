<?php
namespace common\services\offer\logic;

class OfferSkuSave
{
    public $offer_sku;

    public function execute()
    {
        // for example - do something
//        $this->offer->setDefaultCategory();
        // .....


        $this->offer_sku->save();

        return true;
    }
}