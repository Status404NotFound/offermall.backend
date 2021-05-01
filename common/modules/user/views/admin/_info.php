<?php

/**
 * @var yii\web\View $this
 * @var \common\modules\user\models\tables\User $user
 */

?>
<div class="row">
    <div class="col-md-5">
        <p class="m-xs">
            Status:
            <?php if ( $user->getIsOnline($user->id) ):?>
                <span class="label label-primary">Online</span>
            <?php else: ?>
                <span class="label">Offline</span>
            <?php endif ?>
        </p>
        <table class="table small m-b-xs">
            <tr>
                <td><strong><?= Yii::t('user', 'Email') ?>:</strong></td>
                <td class="text-success"><?= $user->email ?></td>
            </tr>
            <tr>
                <td><strong><?= Yii::t('user', 'Last login') ?>:</strong></td>
                <?php if ( empty($user->last_login_at) ): ?>
                    <td class="text-danger"><?= Yii::t('user', 'Never') ?></td>
                <?php else: ?>
                    <td class="text-success"><?= $user->last_login_at ?></td>
                <?php endif ?>
            </tr>
            <tr>
                <td><strong><?= Yii::t('user', 'Location') ?>:</strong></td>
                <?php if ( empty($user->profile->location) ): ?>
                    <td class="text-danger"><?= Yii::t('user', 'Not found') ?></td>
                <?php else: ?>
                    <td class="text-success"><?= $user->profile->location ?></td>
                <?php endif ?>
            </tr>
            <tr>
                <td><strong><?= Yii::t('user', 'Time zone') ?>:</strong></td>
                <?php if ( empty($user->profile->timezone) ): ?>
                    <td class="text-danger"><?= Yii::t('user', 'Not found') ?></td>
                <?php else: ?>
                    <td class="text-success"><?= $user->profile->timezone ?></td>
                <?php endif ?>
            </tr>
            <tr>
                <td><strong><?= Yii::t('user', 'Block status') ?>:</strong></td>
                <?php if ($user->isBlocked): ?>
                    <td class="text-danger"><?= Yii::t('user', 'Blocked at {0}', [$user->blocked_at]) ?></td>
                <?php else: ?>
                    <td class="text-success"><?= Yii::t('user', 'Not blocked') ?></td>
                <?php endif ?>
            </tr>
            <tr>
                <td><strong><?= Yii::t('user', 'Registration time') ?>:</strong></td>
                <td class="text-success"><?= Yii::t('user', 'Registered at {0}', [$user->created_at]) ?></td>
            </tr>
            <tr>
                <td><strong><?= Yii::t('user', 'Confirmation status') ?>:</strong></td>
                <?php if ($user->isConfirmed): ?>
                    <td class="text-success"><?= Yii::t('user', 'Confirmed at {0}', [$user->confirmed_at]) ?></td>
                <?php else: ?>
                    <td class="text-danger"><?= Yii::t('user', 'Unconfirmed') ?></td>
                <?php endif ?>
            </tr>
            <tr>
                <td><strong><?= Yii::t('user', 'Update status') ?>:</strong></td>
                <td class="text-success"><?= Yii::t('user', 'Updated at {0}', [$user->updated_at]) ?></td>
            </tr>
        </table>
    </div>
</div>
