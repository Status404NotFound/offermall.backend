<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 06.02.17
 * Time: 20:46
 */

namespace tds\modules\genform\models\forms;


use tds\modules\genform\models\builder\theme\Theme;
use tds\modules\genform\models\data\DataFormInterface;
use tds\modules\genform\models\data\field\DataField;
use tds\modules\genform\models\data\hidden\DataHidden;
use tds\modules\genform\models\data\pages\DataPages;
use tds\modules\genform\models\pages\PageFormInterface;
use yii\helpers\Html;

abstract class Form implements FormInterface
{
    protected $providerTheme;
    protected $providerPage;

    private $formMethod = 'post';
    private $formAction = '';
    private $formOptions = ['class' => 'genform orderformcdn'];

    /**
     * @var DataHidden $dataHidden это объект который описывает скрытые поля формы.
     */
    private $dataHidden;

    /**
     * @var DataPages $dataPages это обект который описывает страницы
     */
    protected $dataPages;


    public function __construct(DataFormInterface $dataGenform, PageFormInterface $providerPage, Theme $providerTF)
    {
        $this->providerTheme = $providerTF;
        $this->providerPage = $providerPage;

//        $this->formAction = 'http://crm.advertfish.com/form/' . $dataGenform->getHash();
        $this->formAction = 'http://fish.regorder/order';
//        $this->formAction = 'http://regorder.advertfish.com/order';
        $this->dataHidden = $dataGenform->getHidden();
        $this->dataPages = $dataGenform->getPages();
    }

    /**
     * Получить URL отправки формы.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->formAction;
    }

    /**
     * Установить URL отправки формы.
     *
     * @param $action
     */
    public function setAction($action)
    {
        $this->formAction = $action;
    }

    /**
     * Получить опции формы.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->formOptions;
    }

    /**
     * Установить опции формы
     * @param $name
     * @param $value
     */
    public function setOptions($name, $value)
    {
        $this->formOptions[$name] = $value;
    }

    /**
     * Создание структури HTML формы, разделяя скрытые поля и контент формы.
     *
     * @return string
     */
    public function getHTML()
    {
        $form = Html::beginForm($this->getAction(), $this->formMethod, $this->getOptions());
        $form .= $this->hiddenInput();
        $form .= $this->pages();
        $form .= Html::endForm();
        return $form;
    }

    /**
     * Создание скритых полей на основе массива опций.
     *
     * @return string
     */
    protected function hiddenInput()
    {
        $html = '';
        $arrHidden = $this->dataHidden->getFields();
        foreach ($arrHidden as $dataField) {
            /** @var DataField $dataField */
            $html .= $this->providerTheme->getHTML($dataField) . PHP_EOL;
        }

        return $html;
    }

    /**
     * @return string
     */
    protected function pages()
    {
        return $this->providerPage->getPages($this->dataPages, $this->providerTheme);
    }

    public function getJS()
    {
        $JS = $this->providerTheme->getJS() . PHP_EOL;
        $JS .= $this->providerPage->getJS();
        $JS .= <<<JS
JS;

        return $JS;
    }

    public function getCSS()
    {
        $css = $this->providerTheme->getCSS() . PHP_EOL;
        $css .= $this->providerPage->getCSS();
        $css .= '
        /* *** form-style *** */
        form.genform {
            max-width: 400px;
            margin: 0 auto;
            padding: 0px;
            font-size: 13px!important;
            position: relative;
            font-family: Helvetica,Arial,sans-serif!important;
            background-color: #ffffff;
            border: 1px solid #999!important;
            border-image-source: initial;
            border-image-slice: initial;
            border-image-width: initial;
            border-image-outset: initial;
            border-image-repeat: initial;
            -webkit-border-radius: 10px 10px 6px 6px!important;
            -moz-border-radius: 10px 10px 6px 6px!important;
            border-radius: 10px 10px 6px 6px!important;
            outline: 0!important;
            -webkit-box-shadow: 0 3px 7px rgba(0,0,0,0.3)!important;
            -moz-box-shadow: 0 3px 7px rgba(0,0,0,0.3)!important;
            box-shadow: 0 3px 7px rgba(0,0,0,0.3)!important;
            -webkit-background-clip: padding-box!important;
            -moz-background-clip: padding-box!important;
            background-clip: padding-box!important;
            text-align: left;
            color: #444;
            background-color: #f1f1f1;
        }
        /* *** end *** */
        
        /* *** form-header-style *** */
        .genform .form-header {
            color: #fff;
            background-color: #2c3e50;
            border-bottom: 0;
            padding: 9px 15px;
            border-top-right-radius: 6px;
            border-top-left-radius: 6px;
            position: relative;
        }
        .genform .form-header h3 {
            margin: 0!important;
            text-align: center;
            line-height: 30px;
            font-size: 28px;
            font-weight: 700;
        }
        /* *** end *** */
        
        /* *** form-content-style *** */
        .genform .form-content {
            min-height: 50px;
            padding-top: 10px;
            background-color: #fff;
        }
        /* *** end *** */
        ';
        return $css;
    }

}