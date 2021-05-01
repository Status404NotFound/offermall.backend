<?php
/**
 * Created by PhpStorm.
 * User: laring
 * Date: 3/13/17
 * Time: 11:01 PM
 */

$model = new Order; //Это слой моделей
$db = Order::find(); //А это уже слой БД
$db = $db->where(['id' => '1'])->one(); //Снова слой моделей

//getFullName — выносим в модель ActiveRecord, слой моделей
//getUserByName — в ActiveQuery, слой работы с БД
//showFields — в виджет или вью-файл, слой отображения
//cancelCashboxTransaction — бизнес логика, сервисный или доменный слой

//запуск через промежуточный синглтон в сервисном слое.
$order = Order::fineOne(1);
yii::$app->order->cancel($order);