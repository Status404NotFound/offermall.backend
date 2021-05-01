<?php

/** @var string $form */
?>

<div id="content-header">
    <?= \yii\helpers\Html::label('Page: ')?>
    <?= \yii\helpers\Html::dropDownList(
            'listPages', null,
        $listPagesForm,
        ['id' => 'select-list-pages', 'style' => "width: 200px", 'prompt'=>''])?>
</div>
<div id="content-form" class="">
    <div class="col-xs-8" style="margin-top: 50px">

        <?= $form; ?>

    </div>
<!--    <div class="col-xs-4 preview-info" style="background-color: #fff">-->
<!---->
<!--        Info-->
<!---->
<!--    </div>-->
</div>
