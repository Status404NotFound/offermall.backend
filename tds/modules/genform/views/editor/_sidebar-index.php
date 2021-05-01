<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 20.01.17
 * Time: 14:34
 */
use \tds\modules\genform\models\fields\ThemeFields;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var array $listForms */
?>
<div id="menu">
    <div id="menu-wrapper">
        <ul id="menu-titles">
            <?php
            $fist = true;
            foreach ($listForms as $key => $name):?>
            <li class="module <?php if($fist) {$fist = false; echo 'selected';} ?> " id="section_form" data-id="<?= $key?>">
                <img src="" width="30" height="30" alt="icon">
                <h4><?= $name ?></h4><br>
                <span>Test text</span>
                <div class="btn-group btn-group-xs pull-right" role="group" aria-label="...">
                    <a href="<?= Url::toRoute(['editor', 'idForm' => $key])?>" class="btn btn-info">Edit</a>
                    <button type="button" class="btn btn-info" data-toggle="modal"  data-target="#cloneForm">Clone</button>
                    <a href="<?= Url::toRoute(['delete', 'id' => $key])?>" class="btn btn-info">Delete</a>
                </div>
            </li>
            <?php endforeach; ?>
            <li class="no-module disabled"></li>
        </ul>
    </div>
</div>
<div id="clearfix"></div>
<?php Modal::begin([
    'id' => 'cloneForm',
    'header' => '<h4 class="modal-title">Clone form</h4>',
    'toggleButton' => false,
    'size' => Modal::SIZE_SMALL,
    'clientEvents' => [<<<'JS'
$('#cloneForm').on('shown.bs.modal', function () {
    var id = $('#sidebar li.selected').attr('data-id');
    $('#cloneForm #genformtable-name').focus();
    $('#cloneForm #genformtable-id').val(id);
})
JS
]
]);
?>
<div class="cloneForm">
    <?php
    $modelForm = new \tds\modules\genform\tables\GenFormTable();
    $form = ActiveForm::begin(['action' => Url::toRoute('clone')]);
    echo  $form->field($modelForm, 'id',['options' => ['tag' => false],'template' => '{input}'])->hiddenInput();
    echo $form->field($modelForm, 'name')->textInput(['placeholder' => 'Example: Name offer'])->label('Name form');
    echo Html::submitButton(Yii::t('app', 'Clone'), ['class' => 'btn btn-primary pull-right btn-block']);
    ?>
    <div class="clearfix"></div>
    <?php ActiveForm::end();?>
</div><!-- CreateForm -->

<?php Modal::end();?>
