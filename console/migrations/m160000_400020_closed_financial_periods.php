<?php

use yii\db\Migration;

/**
 * Class m600000_400020_closed_financial_periods
 */
class m160000_400020_closed_financial_periods extends Migration
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
        $this->createTable('closed_financial_periods', [
            'period_id' => $this->primaryKey(),
            'date' => $this->string('255'),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'created_by' => $this->integer(11),
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'updated_by' => $this->integer(11),
        ], $tableOptions);

        $this->createIndex('idx-closed_financial_periods-date', 'closed_financial_periods', 'date');
        $this->insertData();
    }

    public function insertData()
    {
        $this->insert('closed_financial_periods', ['date' => '2017-10-01', 'created_at' => '2017-12-01', 'created_by' => 1, 'updated_at' => '2017-12-01', 'updated_by' => 1]);
        $this->insert('closed_financial_periods', ['date' => '2017-11-01', 'created_at' => '2018-01-01', 'created_by' => 1, 'updated_at' => '2018-01-01', 'updated_by' => 1]);
        $this->insert('closed_financial_periods', ['date' => '2017-12-01', 'created_at' => '2018-02-01', 'created_by' => 1, 'updated_at' => '2018-02-01', 'updated_by' => 1]);
        $this->insert('closed_financial_periods', ['date' => '2018-01-01', 'created_at' => '2018-03-01', 'created_by' => 1, 'updated_at' => '2018-03-01', 'updated_by' => 1]);
        $this->insert('closed_financial_periods', ['date' => '2018-02-01', 'created_at' => '2018-04-01', 'created_by' => 1, 'updated_at' => '2018-04-01', 'updated_by' => 1]);
        $this->insert('closed_financial_periods', ['date' => '2018-03-01', 'created_at' => '2018-05-01', 'created_by' => 1, 'updated_at' => '2018-05-01', 'updated_by' => 1]);
        $this->insert('closed_financial_periods', ['date' => '2018-04-01', 'created_at' => '2018-06-01', 'created_by' => 1, 'updated_at' => '2018-06-01', 'updated_by' => 1]);
        $this->insert('closed_financial_periods', ['date' => '2018-05-01', 'created_at' => '2018-07-01', 'created_by' => 1, 'updated_at' => '2018-07-01', 'updated_by' => 1]);
        $this->insert('closed_financial_periods', ['date' => '2018-06-01', 'created_at' => '2018-08-01', 'created_by' => 1, 'updated_at' => '2018-08-01', 'updated_by' => 1]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('idx-closed_financial_periods-date', 'closed_financial_periods');
        $this->dropTable('closed_financial_periods');
    }
}
