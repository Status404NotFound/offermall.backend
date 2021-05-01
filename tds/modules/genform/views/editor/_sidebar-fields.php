<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 20.01.17
 * Time: 14:34
 */
use tds\modules\genform\models\fields\Fields;

/** @var Fields $modelFields */
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
                    $modelFields->getDefaultHTML(Fields::TYPE_TEXT_INPUT),
                    $modelFields->getDefaultHTML(Fields::TYPE_NUMBER_INPUT),
                    $modelFields->getDefaultHTML(Fields::TYPE_TEXTAREA),
                    $modelFields->getDefaultHTML(Fields::TYPE_TELEPHONE),
                    $modelFields->getDefaultHTML(Fields::TYPE_EMAIL),
                ],
//                            'footer' => '<div id="clearfooter"></div>', // the footer label in list-group
                // open its content by default
//                            'contentOptions' => ['class' => 'in']
            ],
            [
                'label' => '<span class="field-icon yes-no"></span> Radios / Checkboxes',
                'content' => [
                    $modelFields->getDefaultHTML(Fields::TYPE_RADIO_HORIZONTAL),
                    $modelFields->getDefaultHTML(Fields::TYPE_RADIO_VERTICAL),
                    $modelFields->getDefaultHTML(Fields::TYPE_CHECK_HORIZONTAL),
                    $modelFields->getDefaultHTML(Fields::TYPE_CHECK_VERTICAL),
                ],
                'contentOptions' => [],
                'options' => [],
            ],
            [
                'label' => '<span class="field-icon dropdown"></span> Select',
                'content' => [
                    $modelFields->getDefaultHTML(Fields::TYPE_SELECT),
                    $modelFields->getDefaultHTML(Fields::TYPE_SELECT_MULTIPLE),
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
