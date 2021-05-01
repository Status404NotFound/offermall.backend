<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 15.01.17
 * Time: 20:40
 */

namespace tds\modules\genform\models\builder;



use tds\modules\genform\models\DataField;
use tds\modules\genform\models\fields\Fields;
use yii\bootstrap\Html;

class EditPageThanks extends BuilderForm
{
    public function __construct($id_form, Fields $modelFields)
    {
        parent::__construct($id_form, $modelFields);
        $this->pages = [BuilderForm::TYPE_PAGE_THANK];
    }

    public function getForm()
    {
        $this->modelFields->setOptionsWrapFields([
            'data-title' => function($dataField){
                return 'Field id: ' . $dataField->optionsId;
            },
            'data-content' => function($dataField){return EditForm::getPopoverForm($dataField);},
            'data-html' => 'true',
            'data-toggle' => 'popover',
            'data-trigger' => 'manual',
            'data-viewport' => '#content',
    //        'data-container => '#content';
            'class' => 'ui-draggable'
        ]);

//        $this->modelFields->setBeforeField('<div class="frontend-field" style="
//                    height: 100%;
//                    position: absolute;
//                    background: rgba(5,172,255,0.05);
//                    width: 97%;
//                    top: -10px;
//                    left: 0;
//                    z-index: 100;
//                    border-width: 1px;
//                    border-style: dashed;
//                    border-color: #222;
//                    margin: 5px 5px 0 5px;
//                "></div>');


        return parent::getForm();
    }

    public static function getPopoverForm(DataField $modelField) {
        $params = array_reverse($modelField->param);

        $form = Html::beginForm(['//genform/edit'], 'post', ['data-pjax' => true, 'class' => 'popover-form']);
        $form .= '<div class="controls">';
        foreach ($params as $key => $param) {
            if (is_string($param) && $param !== '') {
                $form .= Html::label(ucfirst($key), null, ['class' => "popover-label"]);
                $form .= Html::textInput('edit[' . $key . ']', $param, ['class' => "popover-input-text"]);
            } elseif (is_array($param)) {
                $form .= Html::label(ucfirst($key), null, ['class' => "popover-label"]);
                $form .= 'array';
            } else if (is_integer($param) && $key == 'type') {
                $form .= Html::label(
                    ucfirst($key) . ': ' . Fields::getTypeName($param),
                    null,
                    ['class' => "popover-label"]);
            } elseif(is_bool($param)) {
                $form .= Html::label(ucfirst($key), null, ['class' => "popover-label"]);
                $form .= Html::checkbox($key,$param, ['class' => "popover-input-text"]);
            }
        }

        $form .= '<hr/>
                <div class="popover-footer">
                    <a href="javascript:hidePopover()" id="save" class="btn btn-info btn-sm btn-block">Сохранить</a>
                    <a href="javascript:hidePopover()" id="cancel" class="btn btn-danger btn-sm btn-block">Отменить</a>
                <div>
                </div>';


        $form .= Html::endForm();



        return $form;
    }


}