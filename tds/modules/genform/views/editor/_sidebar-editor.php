<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 20.01.17
 * Time: 14:34
 */
use tds\modules\genform\models\builder\theme\Theme;

/** @var Theme $modelTheme */
?>
<div class="content">
    <?= \yii\bootstrap\Collapse::widget([
        'options' => ['id' => 'left-panel'],
        'encodeLabels' => false,
        'items' => [
            // equivalent to the above
            [
                'label' => '<span class="field-icon textfield"></span> Input',
                'content' => [
                    $modelTheme->getDefaultHTML(Theme::TYPE_TEXT_INPUT),
                    $modelTheme->getDefaultHTML(Theme::TYPE_NUMBER_INPUT),
                    $modelTheme->getDefaultHTML(Theme::TYPE_TEXTAREA),
                    $modelTheme->getDefaultHTML(Theme::TYPE_TELEPHONE),
                    $modelTheme->getDefaultHTML(Theme::TYPE_EMAIL),
                ],
//                            'footer' => '<div id="clearfooter"></div>', // the footer label in list-group
                // open its content by default
//                            'contentOptions' => ['class' => 'in']
            ],
            [
                'label' => '<span class="field-icon yes-no"></span> Radios / Checkboxes',
                'content' => [
                    $modelTheme->getDefaultHTML(Theme::TYPE_RADIO_HORIZONTAL),
                    $modelTheme->getDefaultHTML(Theme::TYPE_RADIO_VERTICAL),
                    $modelTheme->getDefaultHTML(Theme::TYPE_CHECK_HORIZONTAL),
                    $modelTheme->getDefaultHTML(Theme::TYPE_CHECK_VERTICAL),
                ],
                'contentOptions' => [],
                'options' => [],
            ],
            [
                'label' => '<span class="field-icon dropdown"></span> Select',
                'content' => [
                    $modelTheme->getDefaultHTML(Theme::TYPE_SELECT),
                    $modelTheme->getDefaultHTML(Theme::TYPE_SELECT_MULTIPLE),
                ],
                'contentOptions' => [],
                'options' => [],
                'footer' => '' // the footer label in list-group
            ],
//                        [
//                            'label' => 'Buttons',
//                            'content' => [
//                                'Anim pariatur cliche...',
//                                'Anim pariatur cliche...'
//                            ],
//                            'contentOptions' => [],
//                            'options' => [],
//                            'footer' => 'Footer' // the footer label in list-group
//                        ],
        ]
    ]);
    ?>
</div>
<div class="remove-fields" style="display:none"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></div>
<div id="clearfix"></div>
<?php
$this->registerJS(<<<JS
    $('#left-panel [data-field_id]').each(function(index,elem) {
        var data = $(elem).attr('data-field_id');
        $(elem).attr('data-field_id',data.substring(0,data.indexOf('-')));
    });
JS
);
?>
