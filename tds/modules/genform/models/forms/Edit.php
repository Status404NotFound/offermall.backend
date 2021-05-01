<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 15.01.17
 * Time: 20:40
 */

namespace tds\modules\genform\models\forms;


use tds\modules\genform\models\data\DataFormInterface;
use tds\modules\genform\models\data\field\DataField;
use tds\modules\genform\models\builder\theme\Theme;
use tds\modules\genform\models\pages\PageForm;
use tds\modules\genform\models\pages\PageFormInterface;
use yii\bootstrap\Html;

class Edit extends Form
{
    public function __construct(DataFormInterface $dataGenform, PageFormInterface $providerPage, Theme $providerTF)
    {
        parent::__construct($dataGenform, $providerPage, $providerTF);

        $form = [ 'form' => $dataGenform->getPages()->getPage('form')];
        $dataGenform->getPages()->setData($form);
    }

    public function getHTML()
    {
        $popoverAttr = [
            'data-title' => function(DataField $dataField){
                return 'Field id: ' . $dataField->optionsId;
            },
            'data-field_type' => function(DataField $dataField){
                return $dataField->getType();
            },
            'data-field_id' => function(DataField $dataField){
                return $dataField->getOptionsId();
            },
            'data-content' => function(DataField $dataField){return Edit::getPopoverForm($dataField);},
            'data-html' => 'true',
            'data-toggle' => 'popover',
            'data-trigger' => 'manual',
            'data-viewport' => '#content',
            'data-container' => '.genform',
            'class' => 'ui-draggable'
        ];
        $panel = '<div class="frontend-field" style="
                    height: 100%;
                    position: absolute;
                    background: rgba(5,172,255,0.05);
                    width: 97%;
                    top: -5px;
                    left: 0;
                    z-index: 100;
                    border-width: 1px;
                    border-style: dashed;
                    border-color: #222;
                    margin: 0 5px 0 5px;
                "></div>';
        $this->providerTheme->setOptionsWrapFields($popoverAttr, PageForm::GROUP_HEADER);
        $this->providerTheme->setOptionsWrapFields($popoverAttr, PageForm::GROUP_CONTENT);
        $this->providerTheme->setOptionsWrapFields($popoverAttr, PageForm::GROUP_FOOTER);
        $this->providerTheme->setOptionsWrapFields($popoverAttr, PageForm::GROUP_DEFAULT);

        $this->providerTheme->setBeforeField('<div class="frontend-field" style="
                    height: 100%;
                    position: absolute;
                    background: rgba(5,172,255,0.05);
                    width: 97%;
                    top: 0;
                    left: 0;
                    z-index: 100;
                    border-width: 1px;
                    border-style: dashed;
                    border-color: #222;
                    margin: 0 5px 0 5px;
                "></div>', PageForm::GROUP_HEADER);
        $this->providerTheme->setBeforeField($panel, PageForm::GROUP_CONTENT);
        $this->providerTheme->setBeforeField('<div class="frontend-field" style="
                    height: 100%;
                    position: absolute;
                    background: rgba(5,172,255,0.05);
                    width: 97%;
                    top: 0;
                    left: 0;
                    z-index: 100;
                    border-width: 1px;
                    border-style: dashed;
                    border-color: #222;
                    margin: 0 5px 0 5px;
                "></div>', PageForm::GROUP_FOOTER);
        $this->providerTheme->setBeforeField($panel, PageForm::GROUP_DEFAULT);

        return parent::getHTML();
    }

    /**
     * @param DataField $modelField
     * @return string
     */
    public static function getPopoverForm(DataField $modelField) {
        $params = array_reverse($modelField->params);

        $form = Html::beginForm(['//genform/edit'], 'post', ['data-pjax' => true, 'class' => 'popover-form']);
        $form .= '<div class="controls">';
        $form .= Html::hiddenInput('id',$modelField->getOptionsId());
        foreach ($params as $key => $param) {
            if (is_string($param) && $param !== '' && $key != 'type') {
                $form .= Html::label(ucfirst($key), null, ['class' => "popover-label"]);
                $form .= Html::textInput($key, $param, ['class' => "popover-input-text"]);
            } elseif (is_array($param)) {
                $form .= Html::label(ucfirst($key), null, ['class' => "popover-label"]);
                $form .= '<div class="fields-items">';
                foreach ($param as $keyValue => $value) :
                    $form .= '<div class="items items-select">';
                    if (is_array($value)) {
                        $form .= '<input class="input-sm addValue inline" type="text" value="' . $value['options']['value'] . '" data-type="a" style="width: 25% !important; display: inline;">';
                        $form .= ' = ';
                        $form .= '<input class="input-sm addValue" type="text" value="' . $value['text'] . '" data-type="b" style="width: 40% !important; display: inline;">';
                    } else {
                        $form .= '<input class="input-sm addValue inline" type="text" value="' . $keyValue . '" data-type="a" style="width: 25% !important; display: inline;">';
                        $form .= ' = ';
                        $form .= '<input class="input-sm addValue" type="text" value="' . $value . '" data-type="b" style="width: 40% !important; display: inline;">';
                    }
                    $form .= '<span class="deleteValue btn btn-danger btn-sm" style="padding-bottom: 5% !important"> (-) </span>';
                    $form .= '</div>';
                endforeach;
                $form .= '</div>';
                $form .= '<br>';
                $form .= '<span id="addValues" class="btn btn-success btn-block" style="margin-top: 5px;">Добавить значения</span>';

            } elseif (is_scalar($param) && $key == 'type') {
                $form .= '<hr>';
                $form .= Html::label(
                    ucfirst($key) . ': ' . Theme::getTypeName($param),
                    null,
                    ['class' => "popover-label"]);
            } elseif(is_bool($param)) {
                $form .= Html::label(ucfirst($key), null, ['class' => "popover-label"]);
                $form .= Html::checkbox($key,$param, ['class' => "popover-input-text"]);
            }
        }

        $form .= <<<'HTML'
<hr/>
                <div class="popover-footer">
                    <a href="javascript:saveProperty()" id="save" class="btn btn-info btn-sm btn-block">Сохранить</a>
                    <a href="javascript:hidePopover()" id="cancel" class="btn btn-danger btn-sm btn-block">Отменить</a>
                <div>
                </div>
HTML;

        $form .= Html::endForm();

        return $form;
    }

    /**
     * @return string
     */
    protected function pages()
    {
        return $this->providerPage->getPage($this->dataPages, 'form', $this->providerTheme);
    }

    public function getCSS()
    {
        return parent::getCSS() . PHP_EOL . <<<CSS

/* *** popover *** */
.popover-content form .btn {
    margin-right: 10px;
	margin-bottom: 10px;
	min-width: 120px;
}
.popover-content form .btn.deleteValue {
    margin: 0px;
    min-width: 15%;
    display: inline-block;
    height: 30px;
}
.popover-content .footer .btn:hover{
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0.05, #77b55a), color-stop(1, #72b352));
    background: -moz-linear-gradient(top, #77b55a 5%, #72b352 100%);
    background: -webkit-linear-gradient(top, #77b55a 5%, #72b352 100%);
    background: -o-linear-gradient(top, #77b55a 5%, #72b352 100%);
    background: -ms-linear-gradient(top, #77b55a 5%, #72b352 100%);
    background: linear-gradient(to bottom, #77b55a 5%, #72b352 100%);
    border: 1px solid #76b558;
    display: inline-block;
    cursor: pointer;
    color: #ffffff;
}

CSS;

    }

}