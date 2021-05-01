<?php
use yii\widgets\Pjax;
use yii\grid\GridView;
?>

<div class="row">
    <div class="col-md-7">
        <?php Pjax::begin([
            'enablePushState' => false,
            'id' => 'operatorTodoList'
        ]); ?>

        <?= GridView::widget([
            'dataProvider' => $flowDataProvider,
            'filterModel' => $flowModel,
            'options' => ['id' => 'coll-list-gw'],
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'contentOptions' => function () {
                        return ['style' => 'width:40px;'];
                    }],
                [
                    'attribute' => 'hash_id',
                    'label' => 'Order ID',
                    'contentOptions' => function () {
                        return ['style' => 'width:80px;'];
                    }
                ],
                [
                    'attribute' => 'created_at',
                    'format' => 'raw',
                    'content' => function ($model) {
                        return Helper::getTimezoneTime($model->created_at, Yii::$app->user->identity->gtm);
                    },
                    'label' => 'Date',
                    'contentOptions' => ['style' => 'width:230px;'],
                    'filter' => DateRangePicker::widget([
                        'name' => 'CallConversionSearch[created_at]',
                        'presetDropdown' => true,
//									'hideInput'=>true,
                        'value' => $flowModel->created_at,
                        'convertFormat' => true,
                        'pluginOptions' => [
                            'locale' => ['format' => 'd.m.Y'],
                            'opens' => 'center',
                            'onchange' => 'this.form.submit()',
//											'drops' => 'up'
                        ]
                    ])
                ],
                [
                    'attribute' => 'fields_data',
                    'format' => 'raw',
                    'content' => function ($model) {
                        $data = Yii::t('app', 'Offer') . ': ' . $model->offer->offer_name;
                        if ($model->name) $data .= '<br />' . Yii::t('app', 'Client name') . ': ' . $model->name;
                        return $data;
                    }
                ],
                [
                    'attribute' => 'input_fields',
                    'label' => '',
                    'format' => 'raw',
                    'content' => function ($model) {
                        $content = '<table class="table table-bordered" style="margin-bottom: 0; zoom: 0.8; max-width: 250px">';
                        foreach ($model->inputFields as $name => $field) {
                            if ($name == 'name' || $name == 'sku') continue;
                            if ($name == 'phone') $field = substr($field, 0, 9) . '****';
                            $content .= '<tr><td>' . Html::encode(ucfirst($name)) . '</td><td>' . Html::encode($field) . '</td></tr>';
                        }
                        $content .= '</table>';

                        return $content;
                    }
                ],
                [
                    'attribute' => 'calls_number',
//                                    'filterInputOptions' => [
//                                        'onchange' => "document.getElementsByClassName('gridview-filter-form')[0].submit()",
//                                        'class' => 'form-control',
//                                    ],
                    'filter' => [
                        0 => '0',
                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => 'More',
                    ],
                    'format' => 'raw',
                    'value' => function ($model) use ($countControl) {
                        $value = '/' . Yii::$app->controller->id . '/grab?id=' . $model->id;
                        if (!$countControl->canTakeLead()) {
                            $value = '/' . Yii::$app->controller->id . '/error?type=take-lead';
                        }

                        switch ($model->calls_number) {
                            case 0:
                            case null:
                                $class_btn = 'btn-danger';
                                break;
                            case 1:
                                $class_btn = 'btn-warning';
                                break;
                            default:
                                $class_btn = 'btn-success';
                                break;
                        }

                        return Html::button('Take this lead!',
                            [
                                'class' => 'btn btn-block ' . $class_btn . ' btn-lg modal-trigger reload-content',
                                'value' => $value,
                                'data-title' => $model->offer->offer_name . ' - ' . Yii::t('app', 'Order details') . '. ID#' . $model->hash_id,
                                'data-reload' => '#operatorFlowList, #operatorTodoList, #operatorPlanList',
                                'title' => 'Call: ' . $model->calls_number,
                            ]
                        );

                    },
                    'contentOptions' => ['style' => 'width:200px;'],
                ],
//							'calls_number',

//		            ['class' => 'yii\grid\ActionColumn'],
            ],
            'rowOptions' => function ($model) {
                if ($model->autolead > 0) {
                    return ['class' => 'warning'];
                }

                return [];
            }
        ]); ?>

        <?php Pjax::end(); ?>
    </div>
</div>
