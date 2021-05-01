<?php

use yii\db\Migration;

/**
 * Handles the creation of table `target_advert_daily_rest`.
 */
class m120000_100004_target_advert_daily_rest extends Migration
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

        $this->createTable('{{%target_advert_daily_rest}}', [
            'target_advert_id' => $this->integer(11)->notNull(),
            'rest' => $this->integer(11)->null(),
            'date' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ], $tableOptions);
        $this->addForeignKey('target_advert_daily_rest_fk_target_advert_id', 'target_advert_daily_rest', 'target_advert_id', 'target_advert', 'target_advert_id', 'CASCADE', 'CASCADE');
        $this->addPrimaryKey('target_advert_daily_rest_pk', 'target_advert_daily_rest', 'target_advert_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('target_advert_daily_rest_fk_target_advert_id', 'target_advert_daily_rest');
        $this->dropTable('{{%target_advert_daily_rest}}');
    }
}
