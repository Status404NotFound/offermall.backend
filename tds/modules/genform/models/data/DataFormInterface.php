<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 07.02.17
 * Time: 0:38
 */

namespace tds\modules\genform\models\data;


use tds\modules\genform\models\data\extension\DataExtension;
use tds\modules\genform\models\data\hidden\DataHidden;
use tds\modules\genform\models\data\pages\DataPages;

interface DataFormInterface
{
    /**
     * @return DataHidden
     */
    public function getHidden();

    /**
     * @return DataPages
     */
    public function getPages();

    /**
     * @return DataExtension
     */
    public function getExtension();

    /**
     * @return integer|string
     */
    public function getHash();
}