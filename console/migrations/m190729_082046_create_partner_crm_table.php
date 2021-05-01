<?php

use yii\db\Migration;

/**
 * Handles the creation of table `partner_crm`.
 */
class m190729_082046_create_partner_crm_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('partner_crm', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'slug' => $this->string(255)->notNull(),
        ]);

        $this->insertData();
    }

    private function insertData()
    {
        $command = 'INSERT INTO partner_crm (name, slug) VALUES ("LP CRM", "lp_crm");';
        Yii::$app->db->createCommand($command)->execute();
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('partner_crm');
    }
}
