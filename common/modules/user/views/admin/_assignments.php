<?php

use common\modules\user\widgets\Assignments;

/**
 * @var yii\web\View $this
 * @var common\modules\user\models\tables\User $user
 */
?>

<?php $this->beginContent('@common/modules/user/views/admin/update.php', ['user' => $user]) ?>

<?= yii\bootstrap\Alert::widget([
    'options' => [
        'class' => 'alert-info alert-dismissible',
    ],
    'body' => Yii::t('user', 'You can assign roles to user by using the form below'),
]) ?>

<?= Assignments::widget(['userId' => $user->id]) ?>

<?php $this->endContent() ?>
