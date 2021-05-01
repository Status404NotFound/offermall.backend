<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model callcenter\models\OperatorActivity */

$this->title = 'Create Operator Activity';
$this->params['breadcrumbs'][] = ['label' => 'Operator Activities', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="operator-activity-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
