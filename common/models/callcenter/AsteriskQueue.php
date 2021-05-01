<?php

namespace common\models\callcenter;

use Yii;

/**
 * This is the model class for table "asterisk_queue".
 *
 * @property string $name
 * @property string $musiconhold
 * @property string $announce
 * @property string $context
 * @property integer $timeout
 * @property string $monitor_type
 * @property string $monitor_format
 * @property string $queue_youarenext
 * @property string $queue_thereare
 * @property string $queue_callswaiting
 * @property string $queue_holdtime
 * @property string $queue_minutes
 * @property string $queue_seconds
 * @property string $queue_lessthan
 * @property string $queue_thankyou
 * @property string $queue_reporthold
 * @property integer $announce_frequency
 * @property integer $announce_round_seconds
 * @property string $announce_holdtime
 * @property integer $retry
 * @property integer $wrapuptime
 * @property integer $maxlen
 * @property integer $servicelevel
 * @property string $strategy
 * @property integer $joinempty
 * @property string $leavewhenempty
 * @property string $autopause
 * @property string $autopausebusy
 * @property integer $autopausedelay
 * @property string $autopauseunavail
 * @property integer $eventmemberstatus
 * @property string $eventwhencalled
 * @property integer $reportholdtime
 * @property integer $memberdelay
 * @property integer $weight
 * @property integer $timeoutrestart
 * @property string $periodic_announce
 * @property integer $periodic_announce_frequency
 * @property integer $ringinuse
 * @property integer $setinterfacevar
 * @property integer $setqueuevar
 * @property integer $setqueueentryvar
 *
 * @property AsteriskQueueMember[] $asteriskQueueMembers
 */
class AsteriskQueue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'asterisk_queue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['timeout', 'announce_frequency', 'announce_round_seconds', 'retry', 'wrapuptime', 'maxlen', 'servicelevel', 'joinempty', 'autopausedelay', 'eventmemberstatus', 'reportholdtime', 'memberdelay', 'weight', 'timeoutrestart', 'periodic_announce_frequency', 'ringinuse', 'setinterfacevar', 'setqueuevar', 'setqueueentryvar'], 'integer'],
            [['name', 'musiconhold', 'announce', 'context', 'monitor_type', 'monitor_format', 'queue_youarenext', 'queue_thereare', 'queue_callswaiting', 'queue_holdtime', 'queue_minutes', 'queue_seconds', 'queue_lessthan', 'queue_thankyou', 'queue_reporthold', 'announce_holdtime'], 'string', 'max' => 20],
            [['strategy'], 'string', 'max' => 128],
            [['leavewhenempty', 'autopause', 'autopausebusy', 'autopauseunavail', 'periodic_announce'], 'string', 'max' => 50],
            [['eventwhencalled'], 'string', 'max' => 6],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Name'),
            'musiconhold' => Yii::t('app', 'Musiconhold'),
            'announce' => Yii::t('app', 'Announce'),
            'context' => Yii::t('app', 'Context'),
            'timeout' => Yii::t('app', 'Timeout'),
            'monitor_type' => Yii::t('app', 'Monitor Type'),
            'monitor_format' => Yii::t('app', 'Monitor Format'),
            'queue_youarenext' => Yii::t('app', 'Queue Youarenext'),
            'queue_thereare' => Yii::t('app', 'Queue Thereare'),
            'queue_callswaiting' => Yii::t('app', 'Queue Callswaiting'),
            'queue_holdtime' => Yii::t('app', 'Queue Holdtime'),
            'queue_minutes' => Yii::t('app', 'Queue Minutes'),
            'queue_seconds' => Yii::t('app', 'Queue Seconds'),
            'queue_lessthan' => Yii::t('app', 'Queue Lessthan'),
            'queue_thankyou' => Yii::t('app', 'Queue Thankyou'),
            'queue_reporthold' => Yii::t('app', 'Queue Reporthold'),
            'announce_frequency' => Yii::t('app', 'Announce Frequency'),
            'announce_round_seconds' => Yii::t('app', 'Announce Round Seconds'),
            'announce_holdtime' => Yii::t('app', 'Announce Holdtime'),
            'retry' => Yii::t('app', 'Retry'),
            'wrapuptime' => Yii::t('app', 'Wrapuptime'),
            'maxlen' => Yii::t('app', 'Maxlen'),
            'servicelevel' => Yii::t('app', 'Servicelevel'),
            'strategy' => Yii::t('app', 'Strategy'),
            'joinempty' => Yii::t('app', 'Joinempty'),
            'leavewhenempty' => Yii::t('app', 'Leavewhenempty'),
            'autopause' => Yii::t('app', 'Autopause'),
            'autopausebusy' => Yii::t('app', 'Autopausebusy'),
            'autopausedelay' => Yii::t('app', 'Autopausedelay'),
            'autopauseunavail' => Yii::t('app', 'Autopauseunavail'),
            'eventmemberstatus' => Yii::t('app', 'Eventmemberstatus'),
            'eventwhencalled' => Yii::t('app', 'Eventwhencalled'),
            'reportholdtime' => Yii::t('app', 'Reportholdtime'),
            'memberdelay' => Yii::t('app', 'Memberdelay'),
            'weight' => Yii::t('app', 'Weight'),
            'timeoutrestart' => Yii::t('app', 'Timeoutrestart'),
            'periodic_announce' => Yii::t('app', 'Periodic Announce'),
            'periodic_announce_frequency' => Yii::t('app', 'Periodic Announce Frequency'),
            'ringinuse' => Yii::t('app', 'Ringinuse'),
            'setinterfacevar' => Yii::t('app', 'Setinterfacevar'),
            'setqueuevar' => Yii::t('app', 'Setqueuevar'),
            'setqueueentryvar' => Yii::t('app', 'Setqueueentryvar'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAsteriskQueueMembers()
    {
        return $this->hasMany(AsteriskQueueMember::className(), ['queue_name' => 'name']);
    }
}
