<?php
/***************** Заметки\шпаргалки *****************/

/***************** Работа с базами данных *****************/

/*********************************** Репликация и разделение запросов на чтение и запись ************************************/
//https://yiiframework.com.ua/ru/doc/guide/2/db-dao/#read-write-splitting

/*********************************** Получение схемы таблицы ************************************/
$table = Yii::$app->db->getTableSchema('product');

/*****************TRANSACTIONS*****************/
//Следующий код показывает типичное использование транзакций:
Yii::$app->db->transaction(function ($db) {
    $db->createCommand($sql1)->execute();
    $db->createCommand($sql2)->execute();
    // ... executing other SQL statements ...
});

//Код выше эквивалентен приведённому ниже.
//Разница в том, что в данном случае мы получаем больше контроля над обработкой ошибок:

$db = Yii::$app->db;
$transaction = $db->beginTransaction();


// Ради совместимости с PHP 5.x и PHP 7.x использованы два блока catch.
// \Exception реализует интерфейс \Throwable interface начиная с PHP 7.0.
// Если вы используете только PHP 7 и новее, можете пропустить блок с \Exception.
try {
    $db->createCommand($sql1)->execute();
    $db->createCommand($sql2)->execute();
    // ... executing other SQL statements ...

    $transaction->commit();
} catch (\Exception $e) {
    $transaction->rollBack();
    throw $e;
} catch (\Throwable $e) {
    $transaction->rollBack();
}


/*** Пакетная выборка данных ***/
/*** https://yiiframework.com.ua/ru/doc/guide/2/db-query-builder/#batch-query ***/

/*** При работе с большими объемами данных, методы на подобие yii\db\Query::all() не подходят,
 * потому что они требуют загрузки всех данных в память. Чтобы сохранить требования к памяти минимальными,
 * Yii предоставляет поддержку так называемых пакетных выборок.
 * Пакетная выборка делает возможным курсоры данных и выборку данных пакетами.
 ***/
