<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 10.02.17
 * Time: 11:06
 */

namespace tds\modules\genform\models\data;


interface FormFileSaveInterface
{
    /**
     * @param string $fileSource
     * @param string $fileExtension
     * @return void
     */
    public function genFile($fileSource, string $fileExtension);
}