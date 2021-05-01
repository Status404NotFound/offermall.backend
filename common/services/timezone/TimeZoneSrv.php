<?php
namespace common\services\timezone;
use Yii;

class TimeZoneSrv
{
    public $time_zone_offset;

    public function __construct()
    {
        $this->time_zone_offset = $this->getTimeZoneOffset();
    }

    private function getTimeZoneOffset()
    {
        $dt = new \DateTime('now', new \DateTimeZone(Yii::$app->user->identity->profile->timezone));
        $offset = $dt->format('P');
        return $offset;
    }
}