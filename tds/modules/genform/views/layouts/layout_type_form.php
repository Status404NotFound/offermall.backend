<?php

use tds\modules\genform\assets\GenformBuildAsset;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use tds\modules\genform\assets\GenformAsset;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

GenformBuildAsset::register($this);
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

    <div id="layout-wrapper">

        <div id="layout-header-wrapper">
            <div id="layout-header">
                <div id="header-logotype">
                    <h3 id="logo" style="color:#fff; font-weight: bold;">GenForm</>
                    <h3 id="logo-end" style="color: #75cbc9; font-weight: bold; font-size: 24px">|</h3>
                </div>
            </div>
            <div id="layout-subheader">
                <div id="state-form" style="">
                    <div id="form-info">
                        <a href="#" class="back-to-workspace-button">
                            <span class="back-arrow"></span>
                        </a>
                        <span id="form-title" class="form-title__ellipsis">
                              <div class="layout-name" data-reactid=".4">
                                  <span class="typeform-name" data-reactid=".4.0">Try PRO</span>
                              </div>
                          </span>
                        <div id="form-trial" class="component-label emphasized"></div>
                    </div>
                    <div class="layout-state-navigation-wrapper">
                        <div class="layout-state-navigation">
                            <ul class="navigation">
                                <li id="menu-item-form" class="active">
                                    <a href="#" data-translation="tab-forms-build">Build</a>
                                </li>
                                <li id="menu-item-design">
                                    <a href="#" data-translation="tab-forms-design">Design</a>
                                </li>
                                <li id="menu-item-configure">
                                    <a href="#" data-translation="tab-forms-configure">Configure</a>
                                </li>
                                <li id="menu-item-distribute">
                                    <a href="#" data-translation="tab-forms-publish">Share</a>
                                </li>
                                <li id="menu-item-analyze">
                                    <a href="#" data-translation="tab-forms-results">Analyze</a>
                                </li>
                                <div style="clear: both;"></div>
                            </ul>
                        </div>
                    </div>

                    <div id="layout-form-buttons">
                        <div class="form-button-wrapper">
                            <a id="form-preview-button" class="admin-button turquoise header" target="_form_preview" href="#">
<!--                                <img src="./Form _files/ICON-link.png" width="18" height="16">-->
                                <span class="icon-link" style="width:18px; height:16px"></span>
                                <b data-translation="view-typeform-button">View my form</b>
                            </a>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="layout-aux" class="" style="">
            <div class="layout-gradient"></div>
            <div class="content"></div>
        </div>

        <div id="layout" class="" style="">
            <div id="wrapper">
                <div id="containment">&nbsp;</div>
                <div id="content">
                    <div class="content">
                        Content
                    </div>
                </div>

                <div id="fieldsbar">
                    Content footer
                </div>

                <div id="sidebar">

                    <div class="content">

                        Content Sidebar

                    </div>

                    <div id="clearfooter"></div>
                </div>

            </div>
        </div>
    </div>

    <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
