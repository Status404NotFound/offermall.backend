<?php

namespace common\modules\user\helpers;

use yii\helpers\ArrayHelper;

/**
 * Timezone helper.
 *
 * @author makandy <makandy42@gmail.com>
 */
class Timezone
{

    /**
     * Get all of the time zones with the offsets sorted by their offset
     *
     * @return array
     */
    public static function getAll()
    {
        $timeZones = [];
        $timeZoneIdentifiers = \DateTimeZone::listIdentifiers();

        foreach ($timeZoneIdentifiers as $timeZone) {
            $date = new \DateTime('now', new \DateTimeZone($timeZone));
            $offset = $date->getOffset() / 60 / 60;
            $timeZones[] = [
                'timezone' => $timeZone,
                'name' => "{$timeZone} (UTC " . ($offset > 0 ? '+' : '') . "{$offset})",
                'offset' => $offset
            ];
        }

        ArrayHelper::multisort($timeZones, 'offset', SORT_DESC, SORT_NUMERIC);

        return $timeZones;
    }
}
