<?php

use yii\db\Migration;

/**
 * Class m180803_130830_add_area_id_to_customer
 */
class m180803_130830_add_area_id_to_customer extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('customer', 'area_id', $this->integer(11));
        $this->addForeignKey('customer_fk_area_id', 'customer', 'area_id', 'geo_area', 'area_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180803_130830_add_area_id_to_customer cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180803_130830_add_area_id_to_customer cannot be reverted.\n";

        return false;
    }
    */
}
