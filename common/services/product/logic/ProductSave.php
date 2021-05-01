<?php
/**
 * Created by PhpStorm.
 * User: laring
 * Date: 3/16/17
 * Time: 8:23 PM
 */

namespace common\components\product\logic;

class ProductSave
{
    public $product;

    public function execute()
    {
        // for example - do something
        $this->product->setDefaultCategory();
        // .....


        $this->product->save();

        return true;
    }
}