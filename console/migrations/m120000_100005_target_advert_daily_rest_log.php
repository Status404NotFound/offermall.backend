<?php

use yii\db\Migration;

/**
 * Handles the creation of table `target_advert_daily_rest_log`.
 */
class m120000_100005_target_advert_daily_rest_log extends Migration
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

        $this->createTable('{{%target_advert_daily_rest_log}}', [
            'target_advert_id' => $this->integer(11)->notNull(),
            'rest' => $this->integer(11)->null(),
            'date' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'message' => $this->string(1023)->notNull()->defaultValue('OK'),
        ], $tableOptions);
        $this->addForeignKey('target_advert_daily_rest_log_fk_target_advert_id', 'target_advert_daily_rest_log', 'target_advert_id', 'target_advert', 'target_advert_id', 'NO ACTION', 'CASCADE');
//        $this->addPrimaryKey('target_advert_daily_rest_log_pk', 'target_advert_daily_rest_log', 'target_advert_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        $this->dropPrimaryKey('target_advert_daily_rest_log_pk', 'target_advert_daily_rest_log');
        $this->dropForeignKey('target_advert_daily_rest_log_fk_target_advert_id', 'target_advert_daily_rest_log');
        $this->dropTable('{{%target_advert_daily_rest_log}}');
    }
}
