<?php
/* @var $this yii\web\View */
/* @var $site */
/* @var $time */
/* @var $who_is */
?>

<div class="check-index">
    <p>Лендинг был запущен на стороннем сайте: <a href="<?= $site ?>" target="_blank"><?= $site ?></a></p>
    <p>Время запуска: <b><?= $time ?></b></p>
    <p><b>Whois information:</b></p>
    <pre><?=$who_is?></pre>
</div>