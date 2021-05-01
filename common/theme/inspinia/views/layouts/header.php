<?php
use yii\helpers\Url;
?>
<nav class="navbar navbar-static-top  " role="navigation" style="margin-bottom: 0">

    <?php if(!\Yii::$app->user->isGuest): ?>
    <div class="navbar-header">
        <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
        <form role="search" class="navbar-form-custom" action="/search">
            <div class="form-group">
                <input type="text" placeholder="Search for something..." class="form-control" name="top-search" id="top-search">
            </div>
        </form>
    </div>
    <?php endif?>

    <ul class="nav navbar-top-links navbar-right">
        <li>
            <?php if(!\Yii::$app->user->isGuest): ?>
            <span class="m-r-sm text-muted welcome-message"><i class="fa fa-user-circle"></i> Welcome, <?=\Yii::$app->user->identity->username?></span>
            <?php endif?>
        </li>
        <?php if ( Yii::$app->session->has(\common\modules\user\controllers\AdminController::ORIGINAL_USER_SESSION_KEY) ): ?>
            <li>
                <a href="<?=Url::to(['/user/admin/switch'])?>" data-method="post">
                    <i class="fa fa-linux"></i> Return (Admin)
                </a>
            </li>
        <?php endif?>

        <?php if(\Yii::$app->user->isGuest):?>
        <li>
            <a href="<?=Url::to(['/user/login'])?>">
                <i class="fa fa-sign-in"></i> Login
            </a>
        </li>
        <?php else:?>
        <li>
            <a href="<?=Url::to(['/user/logout'])?>" data-method="post">
                <i class="fa fa-sign-out"></i> Log out
            </a>
        </li>

        <?php endif?>
    </ul>

</nav>