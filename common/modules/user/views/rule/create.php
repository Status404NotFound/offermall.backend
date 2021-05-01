<?php

use yii\helpers\Html;

/* @var $this  yii\web\View */
/* @var $model common\modules\user\models\BizRule */

$this->title = Yii::t('user', 'Create Rule');
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Rules'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-item-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?=
    $this->render('_form', [
        'model' => $model,
    ]);
    ?>

</div>
