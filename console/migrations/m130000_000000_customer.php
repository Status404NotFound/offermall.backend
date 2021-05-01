<?php

use yii\db\Migration;

/**
 * Handles the creation of table `customer`.
 */
class m130000_000000_customer extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%customer}}', [
            'customer_id' => $this->primaryKey(11),
            'name' => $this->string(255),

            'country_id' => $this->integer(11)->null(),
            'region_id' => $this->integer(11)->null(),
            'city_id' => $this->integer(11)->null(),
            'address' => $this->string(255)->null(),

            'phone_country_code' => $this->integer(5)->null(),
            'phone' => $this->bigInteger(13)->notNull(),
            'phone_extension' => $this->integer(11)->null(),
            'additional_phone' => $this->bigInteger(13)->null(),
            'phone_string' => $this->text()->null(),

            'email' => $this->string(255)->null(),

            'customer_status' => $this->smallInteger(3)->defaultValue(1),

            'description' => $this->text()->null(),

            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11)->null(),
        ], $tableOptions);

        $this->createIndex('idx-customer_phone', 'customer', 'phone');
        $this->createIndex('idx-customer_email', 'customer', 'email');
        $this->createIndex('idx-customer_updated_at', 'customer', 'updated_at');
//        $this->createIndex('customer_uq1', 'customer', ['phone', 'email'], true);

        $this->addForeignKey('customer_fk_country_id', 'customer', 'country_id', 'countries', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('customer_fk_region_id', 'customer', 'region_id', 'geo_region', 'region_id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('customer_fk_city_id', 'customer', 'city_id', 'geo_city', 'city_id', 'SET NULL', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('customer_fk_city_id', 'customer');
        $this->dropForeignKey('customer_fk_country_id', 'customer');
        $this->dropForeignKey('customer_fk_region_id', 'customer');
//        $this->dropIndex('customer_uq1', 'customer');
        $this->dropIndex('idx-customer_updated_at', 'customer');
        $this->dropIndex('idx-customer_phone', 'customer');
        $this->dropIndex('idx-customer_email', 'customer');

        $this->dropTable('{{%customer}}');
    }
}
