<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 15.01.17
 * Time: 20:33
 */

namespace tds\modules\genform\models;


use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;

abstract class Form
{
    abstract public function renderHTML();
    abstract public function renderCSS();
    abstract public function renderScript();
}