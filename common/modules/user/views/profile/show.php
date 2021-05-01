<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var \yii\web\View $this
 * @var \common\modules\user\models\tables\Profile $profile
 * @var string $content
 */

$this->title = empty($profile->name) ? Html::encode($profile->user->username) : Html::encode($profile->name);
$this->params['breadcrumbs'][] = 'Profile: ' . $this->title;

$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@common/theme/inspinia/assets');
$avatar = isset($profile->avatar_path) ? $profile->avatar_path : $directoryAsset . '/img/default-user-image.png';
?>
<div class="row animated fadeInRight">
    <div class="col-md-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Profile Detail</h5>
            </div>
            <div>
                <div class="ibox-content no-padding border-left-right">
                    <img alt="image" class="img-responsive" src="<?= $avatar ?>">
                </div>
                <div class="ibox-content profile-content">
                    <h4><strong><?php echo $this->title ?></strong></h4>
<!--                    --><?php //if (!empty($profile->location)): ?>
<!--                        <p><i class="fa fa-map-marker"></i> --><?php //echo Html::encode($profile->location) ?><!--</p>-->
<!--                    --><?php //endif; ?>
                    <hr class="hr-line-dashed">
                    <ul class="list-group clear-list">
                        <li class="list-group-item fist-item">
                            <span class="pull-right">
                                <a href="<?= Url::to(['/user/settings/account'])?>" class="btn btn-xs btn-white">
                                    <i class="fa fa-edit"></i>
                                    Edit
                                </a>
                            </span>
                            <strong> Account </strong>
                        </li>
                        <li class="list-group-item">
                            <span class="pull-right"> <?= $user->username ?> </span>
                            Username
                        </li>
                        <li class="list-group-item">
                            <span class="pull-right"> <?= $user->email ?> </span>
                            Email
                        </li>
                    </ul>
                    <hr>
                    <ul class="list-group clear-list">
                        <li class="list-group-item fist-item">
                            <span class="pull-right">
                                <a href="<?= Url::to(['/user/settings/profile'])?>" class="btn btn-xs btn-white">
                                    <i class="fa fa-edit"></i>
                                    Edit
                                </a>
                            </span>
                            <strong> Profile </strong>
                        </li>
                        <?php if ( !empty($profile->name) ): ?>
                        <li class="list-group-item">
                            <span class="pull-right"> <?= $profile->name ?> </span>
                            Name
                        </li>
                        <?php endif;?>
                        <?php if ( !empty($profile->location) ): ?>
                        <li class="list-group-item">
                            <span class="pull-right"> <?= $profile->location ?> </span>
                            Location
                        </li>
                        <?php endif;?>
                        <?php if ( !empty($profile->timezone) ): ?>
                        <li class="list-group-item">
                            <span class="pull-right"> <?= $profile->timezone ?> </span>
                            Time zone
                        </li>
                        <?php endif;?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Inform</h5>
            </div>
            <div class="ibox-content">

                <?= $content?>

            </div>
        </div>

    </div>
</div>