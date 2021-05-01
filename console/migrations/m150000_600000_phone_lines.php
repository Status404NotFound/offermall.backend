 <?php

use yii\db\Migration;

class m150000_600000_phone_lines extends Migration
{
    public function safeUp()
    {
        $this->createTable('phone_lines', [
            'id' => $this->primaryKey(),
            'owner_id' => $this->integer(4),
            'line' => $this->integer(3),
            'country_id' => $this->integer(4),
            'asterisk_id' => $this->string(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);

        $this->createIndex('Index-ownerid_line', 'phone_lines', ['owner_id', 'line'], true);
    }

    public function safeDown()
    {
        $this->dropIndex('Index-ownerid_line', 'phone_lines');
        $this->dropTable('phone_lines');
    }

}
