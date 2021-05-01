<?php
/* @var $this yii\web\View */
/* @var $data[] */
?>

<div class="webmaster-notify">
    <b>Dear, <?=$data['username']?></b>
    <p>
        Offer: <?=$data['offer_name']?> changed the status to <b><?=$data['offer_status']?></b>
    </p>
</div>