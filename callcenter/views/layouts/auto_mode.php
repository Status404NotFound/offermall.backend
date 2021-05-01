<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use callcenter\assets\AppAsset;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title>AF CallCenter</title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'AF Auto CC',
        'brandUrl' => Yii::$app->homeUrl . 'auto-call',
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
    } else {
        $menuItems = [
            ['label' => 'Do Call', 'url' => ['/site/about']],
            ['label' => 'Delivery', 'url' => ['/site/about']],
        ];
        $menuItems[] = '<li>'
            . Html::beginForm(['/site/logout'], 'post')
            . Html::submitButton(
                'Logout (' . Yii::$app->user->identity->username . ')',
                ['class' => 'btn btn-link logout']
            )
            . Html::endForm()
            . '</li>';
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <div class="row status-panel">
            <div class="col-md-4 text-center status onpause">
                <?= \yii\bootstrap\Html::button('ON PAUSE', [
                    'class' => 'btn btn-success',
                    'onclick' => 'setOperatorStatus(3)',
                ]) ?>
            </div>
            <?php if (Yii::$app->request->url == '/auto-call'): ?>
            <div class="col-md-4 text-center status onair">
                <?= \yii\bootstrap\Html::button('ON AIR', [
                    'class' => 'btn btn-info',
                    'onclick' => 'setOperatorStatus(1)',
                ]) ?>
            </div>
            <?php endif; ?>
            <div class="col-md-4 text-center status onworkpause">
                <?= \yii\bootstrap\Html::button('ON WORK PAUSE', [
                    'class' => 'btn btn-warning',
                    'onclick' => 'setOperatorStatus(4)',
                ]) ?>
            </div>
            <div class="col-md-12 text-center status-calling">
                <h1>YOU ARE CALLING!</h1><br>
                <?= \yii\bootstrap\Html::button('STOP CALL', ['class' => 'btn btn-warning', 'onclick' => 'setOperatorStatus(3)']) ?>
            </div>
            <div class="col-md-12 text-center status-onair">
                <h1>YOU ARE ONAIR!</h1><br>
            </div>
        </div>
        <!--<div class="row top-pannel">
            <div class="col-md-4 status-block">
                <?php
                echo \yii\bootstrap\Html::dropDownList('status_list', Yii::$app->operator->status, Yii::$app->operator->getOperatorStatus(true, false), ['id' => 'record-status', 'options' => ['class' => 'ddd']]) . '<br>';
                echo \yii\bootstrap\Html::button('change', ['class' => 'btn btn-success element']);
                ?>
            </div>
        </div>
        -->
        <div class="col-md-12">
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">Crmka CallCenter <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
