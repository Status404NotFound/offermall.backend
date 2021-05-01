<?php

use yii\db\Migration;

/**
 * Class m180426_101722_asterisk_queue
 */
class m180426_101722_asterisk_queue extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute("CREATE TABLE `asterisk_queue` (
     `name` VARCHAR(20) NOT NULL COLLATE 'utf8_unicode_ci',
     `musiconhold` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `announce` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `context` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `timeout` INT(11) NULL DEFAULT '15',
     `monitor_type` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `monitor_format` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `queue_youarenext` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `queue_thereare` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `queue_callswaiting` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `queue_holdtime` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `queue_minutes` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `queue_seconds` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `queue_lessthan` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `queue_thankyou` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `queue_reporthold` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `announce_frequency` TINYINT(2) NULL DEFAULT '0',
     `announce_round_seconds` INT(11) NULL DEFAULT '0',
     `announce_holdtime` VARCHAR(20) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `retry` TINYINT(2) NULL DEFAULT '5',
     `wrapuptime` INT(11) NULL DEFAULT '0',
     `maxlen` INT(11) NULL DEFAULT '0',
     `servicelevel` INT(11) NULL DEFAULT '60',
     `strategy` VARCHAR(128) NULL DEFAULT 'ringall' COLLATE 'utf8_unicode_ci',
     `joinempty` TINYINT(1) NULL DEFAULT '0',
     `leavewhenempty` VARCHAR(50) NULL DEFAULT 'no' COLLATE 'utf8_unicode_ci',
     `autopause` VARCHAR(50) NULL DEFAULT 'no' COLLATE 'utf8_unicode_ci',
     `autopausebusy` VARCHAR(50) NULL DEFAULT 'no' COLLATE 'utf8_unicode_ci',
     `autopausedelay` TINYINT(4) NULL DEFAULT '0',
     `autopauseunavail` VARCHAR(50) NULL DEFAULT 'yes' COLLATE 'utf8_unicode_ci',
     `eventmemberstatus` TINYINT(1) NULL DEFAULT '0',
     `eventwhencalled` VARCHAR(6) NULL DEFAULT 'vars' COLLATE 'utf8_unicode_ci',
     `reportholdtime` TINYINT(1) NULL DEFAULT '0',
     `memberdelay` INT(11) NULL DEFAULT '0',
     `weight` INT(11) NULL DEFAULT '0',
     `timeoutrestart` TINYINT(1) NULL DEFAULT '0',
     `periodic_announce` VARCHAR(50) NULL DEFAULT '' COLLATE 'utf8_unicode_ci',
     `periodic_announce_frequency` INT(11) NULL DEFAULT '0',
     `ringinuse` TINYINT(1) NULL DEFAULT '0',
     `setinterfacevar` TINYINT(1) NULL DEFAULT '0',
     `setqueuevar` TINYINT(1) NOT NULL DEFAULT '1',
     `setqueueentryvar` TINYINT(1) NOT NULL DEFAULT '1',
     PRIMARY KEY (`name`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB; ");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {

    }

}
