<?php


/**
 * @var \common\modules\user\models\tables\User   $user
 * @var \common\modules\user\models\tables\Token  $token
 */
?>
<?= Yii::t('user', 'Hello') ?>,

<?= Yii::t('user', 'We have received a request to reset the password for your account on {0}', Yii::$app->name) ?>.
<?= Yii::t('user', 'Please click the link below to complete your password reset') ?>.

<?= $token->url ?>

<?= Yii::t('user', 'If you cannot click the link, please try pasting the text into your browser') ?>.

<?= Yii::t('user', 'If you did not make this request you can ignore this email') ?>.
