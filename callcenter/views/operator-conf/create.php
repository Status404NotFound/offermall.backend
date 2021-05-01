<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model callcenter\models\OperatorConf */

$this->title = 'Create Operator Conf';
$this->params['breadcrumbs'][] = ['label' => 'Operator Confs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="operator-conf-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
