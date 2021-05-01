<?php

namespace tds\modules\genform;


use common\helpers\FishHelper;
use tds\modules\genform\assets\GenformAsset;
use yii\web\View;

/**
 * genform module definition class
 * TODO: Правила разбора урла перенести в конфиг модуля.
 * TODO: Ограничить доступ пользователя на форму не пренадлежащую ему.
 *
 */
class GenForm extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'tds\modules\genform\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        \Yii::setAlias('@genform', dirname(__DIR__) . '/genform');
        $this->layoutPath = '@genform/views/layouts';
        $this->layout = 'layout';


        // custom initialization code goes here
    }
}
