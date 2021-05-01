<?php

use tds\modules\genform\assets\GenformAsset;
use yii\helpers\Url;

GenformAsset::register($this);

$this->beginContent('@app/views/layouts/admin.php'); ?>

    <div id="layout-wrapper">
        <div id="layout-header-wrapper">
            <div id="layout-header">
                <div id="header-logotype">
                    <h3>GenForm editor</h3>
                </div>
            </div>
            <div id="layout-subheader">
                <div id="state-form" style="">
                    <div id="form-info">

                    </div>
                    <div class="layout-state-navigation-wrapper">
                        <div class="layout-state-navigation">
                            <?php //TODO Решение в лоб..?>
                            <ul class="navigation">
                                <li <?php if (Yii::$app->request->url == '/genform/'.Yii::$app->controller->id.'/build') echo 'class="active"'?>>
                                    <a href="<?= Url::toRoute('/genform/editor/build')?>" data-translation="tab-forms-build">Build Form</a>
                                </li>
                                <li <?php if (Yii::$app->request->url == '/genform/'.Yii::$app->controller->id.'/page-thanks') echo 'class="active"'?>>
                                    <a href="<?= Url::toRoute('/genform/editor/page-thanks')?>" data-translation="tab-forms-build">Page thanks</a>
                                </li>
                                <li <?php if (Yii::$app->request->url == '/genform/'.Yii::$app->controller->id.'/calculator') echo 'class="active"'?>>
                                    <a href="<?= Url::toRoute('/genform/editor/calculator')?>" data-translation="tab-forms-build">Calculator</a>
                                </li>
                                <li <?php if (Yii::$app->request->url == '/genform/'.Yii::$app->controller->id.'/design') echo 'class="active"'?>>
                                    <a href="<?= Url::toRoute('/genform/editor/design')?>" data-translation="tab-forms-design">Design</a>
                                </li>
                                <li <?php if (Yii::$app->request->url == '/genform/'.Yii::$app->controller->id.'/module') echo 'class="active"'?>>
                                    <a href="<?= Url::toRoute('/genform/editor/module')?>" data-translation="tab-forms-design">Module</a>
                                </li>
                                <li <?php if (Yii::$app->request->url == '/genform/'.Yii::$app->controller->id.'/options') echo 'class="active"'?>>
                                    <a href="<?= Url::toRoute('/genform/editor/options')?>" data-translation="tab-forms-publish">Options</a>
                                </li>
                                <li <?php if (Yii::$app->request->url == '/genform/'.Yii::$app->controller->id.'/source') echo 'class="active"'?>>
                                    <a href="<?= Url::toRoute('/genform/editor/source')?>" data-translation="tab-forms-publish">Source</a>
                                </li>
                                <div style="clear: both;"></div>
                            </ul>
                        </div>
                    </div>

                    <div id="layout-form-buttons">
                        <div class="form-button-wrapper">
                            <a id="form-preview-button" class="admin-button turquoise header typeform-share" href="<?= Url::toRoute('/genform/editor/test')?>">
                                <b data-translation="view-typeform-button">View demo form</b>
                            </a>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                </div>
            </div>
        </div>

        <?php echo $content; ?>

    </div>

<?php $this->endContent(); ?>