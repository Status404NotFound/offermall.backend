<?php

use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use tds\modules\genform\assets\GenformAsset;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

GenformAsset::register($this);
/* @var $this yii\web\View */
/* @var $content string */
//$this->registerCssFile('@genform/web/css/bootstrap.css');


$getIdForm = Yii::$app->request->get('idForm');
$idForm = is_null($getIdForm)? 0:  $getIdForm;
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>

    <div id="layout-wrapper" class="container-fluid">
        <div id="layout-header-wrapper">
            <div id="layout-header">
                <div id="header-logotype" class="">
                    <h3 class="text-center">GenForm editor</h3>
                </div>
            </div>
            <?php if (Yii::$app->controller->action->id !== 'index'):?>
            <div id="layout-subheader">
                <div id="layout-menu" class="">
                    <div class="col-sm-2">
                        <div id="layout-left-navigation">
                            <a href="<?= Url::toRoute('index')?>" class="back-to-workspace-button">
                                <img id="back-arrow" src="">
                            </a>
                            <span id="form-title" class="form-title__ellipsis">
                                <div class="layout-name">
                                    <span class="typeform-name">
                                        Form: <?= ArrayHelper::getValue($this->params,'nameForm','Not Name')?>
                                    </span>
                                </div>
                            </span>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <div class="layout-state-navigation">
                            <?php //TODO Решение в лоб..?>
                            <ul class="navigation">
                                <li <?php if (Yii::$app->controller->action->id == 'editor') echo 'class="active"'?>>
                                    <a href="<?= Url::toRoute(['editor', 'idForm' => $idForm])?>" data-translation="tab-forms-build">Editor</a>
                                </li>
                                <li <?php if (Yii::$app->controller->action->id == 'calculator') echo 'class="active"'?>>
                                    <a href="<?= Url::toRoute(['calculator', 'idForm' => $idForm])?>" data-translation="tab-forms-build">Calculator</a>
                                </li>
                                <li <?php if (Yii::$app->controller->action->id == 'design') echo 'class="active"'?>>
                                    <a href="<?= Url::toRoute(['design', 'idForm' => $idForm])?>" data-translation="tab-forms-design">Design</a>
                                </li>
                                <li <?php if (Yii::$app->controller->action->id == 'module') echo 'class="active"'?>>
                                    <a href="<?= Url::toRoute(['module', 'idForm' => $idForm])?>" data-translation="tab-forms-design">Module</a>
                                </li>
                                <li <?php if (Yii::$app->controller->action->id == 'options') echo 'class="active"'?>>
                                    <a href="<?= Url::toRoute(['options', 'idForm' => $idForm])?>" data-translation="tab-forms-publish">Options</a>
                                </li>
                                <li <?php if (Yii::$app->controller->action->id == 'source') echo 'class="active"'?>>
                                    <a href="<?= Url::toRoute(['source', 'idForm' => $idForm])?>" data-translation="tab-forms-publish">Source</a>
                                </li>
                                <div style="clear: both;"></div>
                            </ul>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="button-wrapper">
                            <a class="btn btn-success btn-xs" target="_blank" href="<?= Url::toRoute(['test', 'idForm' => $getIdForm])?>" role="button">View demo form</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div id="layout-subheader">
                <div id="layout-menu" class="">
                    <div class="col-sm-4">
                        <?php Modal::begin([
                            'id' => 'newForm',
                            'header' => '<h4 class="modal-title">New form</h4>',
                            'toggleButton' => [
                                'label' => 'New form',
                                'class' => 'btn btn-success btn-lg',
                                'style' => 'width: 80%; margin: 7px auto;display: block;"'],
                                'size' => Modal::SIZE_SMALL,
                                'clientEvents' => ['$(\'#newForm\').on(\'shown.bs.modal\', function () {$(\'#newForm #genformtable-name\').focus() })']
                        ]);
                        ?>
                        <div class="CreateForm">
                            <?php $form = ActiveForm::begin(['action' => Url::toRoute('create')]);
                                echo $form->field(new \tds\modules\genform\tables\GenFormTable(), 'name')
                                    ->textInput(['placeholder' => 'Example: Name offer'])
                                    ->label('Name form');
                                echo Html::submitButton(Yii::t('app', 'Create'), ['class' => 'btn btn-primary pull-right btn-block']);
                            ?>
                            <div class="clearfix"></div>
                            <?php ActiveForm::end();?>
                        </div><!-- CreateForm -->

                       <?php Modal::end();?>
                    </div>
                    <div class="col-sm-8">
                    </div>

                </div>
            </div>
            <?php endif;?>
        </div>
        <div id="layout-content">

            <?php echo $content; ?>

        </div>
        <div id="fieldsbar"></div>
    </div>

    <?php $this->endBody() ?>
    <style>
        .popover {
            background-color: #eee;
            min-width: 250px;
        }
        .popover-title {
            background-color: #37404a;
            color: #fff;
            font-weight: 600;
            margin: -1px;
        }
        .popover label {
            display: block;
            clear: both;
            font-weight: 100;
            margin-bottom: 3px;
            color: #666 !important;
            font-family: "Avenir Next W01", Arial, "sans-serif";
            font-size: 1em;
            text-align: left;
        }
        .popover input[type="text"],
        .popover textarea,
        .popover select
        {
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            background-color: #fff;
            color: #333;
            cursor: text;
            border: 1px solid #cccccc;
            -moz-border-radius: 2px;
            border-radius: 2px;
            font-family: "Avenir Next W01", Arial, "sans-serif";
            font-size: 1em;
            background-clip: padding-box;
            -moz-background-clip: padding-box;
            -webkit-background-clip: padding-box;
            padding: 5px;
            display: block;
            margin: 0 5px 10px 0;
            width: 100%;
        }
        .popover input[type="text"]:focus,
        .popover textarea:focus,
        .popover select:focus
        {
            -webkit-transition: 0.2s ease-in-out;
            -moz-transition: 0.2s ease-in-out;
            background: #FFFFFF !important;
            outline: none;
            box-shadow: 0px 0px 6px 0px #669696;
            border: 1px solid #669696;
        }
        .popover hr {
            border-top: 1px solid #CfCfCf;
        }

        body {
            font-size: 11pt;
            font-family: "Avenir Next W01", Arial, "sans-serif";
            /*overflow-x: hidden !important;*/
        }
    </style>
    </body>
    </html>
<?php $this->endPage() ?>
