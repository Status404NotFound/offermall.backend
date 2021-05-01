<?php

use yii\db\Migration;

/**
 * Class m180426_101741_asterisk_queue_member
 */
class m180426_101741_asterisk_queue_member extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE `asterisk_queue_member` (
      `uniqueid` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `membername` VARCHAR(40) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
      `queue_name` VARCHAR(128) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
      `interface` VARCHAR(128) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
      `penalty` INT(11) NULL DEFAULT '0',
      `paused` INT(11) NULL DEFAULT '0',
      PRIMARY KEY (`uniqueid`),
      UNIQUE INDEX `queue_interface` (`queue_name`, `interface`),
      CONSTRAINT `FK_queue_members_queues` FOREIGN KEY (`queue_name`) REFERENCES `asterisk_queue` (`name`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
