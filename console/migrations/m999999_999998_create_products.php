<?php

use yii\db\Migration;
use yii\db\Schema;

class m999999_999998_create_products extends Migration
{
    public function up()
    {
        /** CREATE PRODUCTS  **/
//        Yii::$app->db->createCommand('INSERT INTO product (product_name, category, img, description, visible, updated_by, created_by)
//VALUES ("Product#1", 1, "", "", 1, 1, 1 )')->execute();
//        Yii::$app->db->createCommand('INSERT INTO product (product_name, category, img, description, visible, updated_by, created_by)
//VALUES ("Product#2", 1, "", "", 1, 1, 1 )')->execute();
//        Yii::$app->db->createCommand('INSERT INTO product (product_name, category, img, description, visible, updated_by, created_by)
//VALUES ("Product#3", 1, "", "Description", 1, 1, 1 )')->execute();
//        /** CREATE SKUS  **/
//        $products = Yii::$app->db->createCommand('SELECT product_id FROM product')->queryAll();
//        foreach ($products as $product) {
//            for ($i = 1; $i <= 5; $i++) {
//                Yii::$app->db->createCommand('INSERT INTO product_sku (product_id, sku_name, sku_alias, color, active, updated_by)
//VALUES (' . $product['product_id'] . ', "' . $product['product_id'] . '_name_' . $i . '", "' . $product['product_id'] . '_alias", "color", 1, 1)')->execute();
//            }
//        }
    }

    public function safeDown()
    {
//        parent::safeDown();
    }
}
