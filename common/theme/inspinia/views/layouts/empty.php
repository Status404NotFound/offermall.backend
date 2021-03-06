<?php

/* @var $this \yii\web\View */
/* @var $content string */

use common\theme\inspinia\AppAsset;
use yii\helpers\Html;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>

    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body class="gray-bg">
<?php $this->beginBody() ?>

    <?=$content?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
