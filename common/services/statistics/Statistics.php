<?php
namespace common\services\statistics;

abstract class Statistics
{
    /**
     * @return mixed
     */
    abstract function statisticsQuery();

    /**
     * @param $post
     * @return mixed
     */
    abstract function getStatistics($post);

    /**
     * @param $rows
     * @return mixed
     */
    abstract function getTotalRow($rows);

    /**
     * @param $numerator
     * @param $denominator
     * @return float|int
     */
    public function ratio($numerator, $denominator)
    {
        return $denominator > 0 ? round($numerator * 100 / $denominator, 2) : 0;
    }

    /**
     * @return array
     */
    public function hoursMatrix()
    {
        return [
            '00:00-00:59' => '00:00-00:59',
            '01:00-01:59' => '01:00-01:59',
            '02:00-02:59' => '02:00-02:59',
            '03:00-03:59' => '03:00-03:59',
            '04:00-04:59' => '04:00-04:59',
            '05:00-05:59' => '05:00-05:59',
            '06:00-06:59' => '06:00-06:59',
            '07:00-07:59' => '07:00-07:59',
            '08:00-08:59' => '08:00-08:59',
            '09:00-09:59' => '09:00-09:59',
            '10:00-10:59' => '10:00-10:59',
            '11:00-11:59' => '11:00-11:59',
            '12:00-12:59' => '12:00-12:59',
            '13:00-13:59' => '13:00-13:59',
            '14:00-14:59' => '14:00-14:59',
            '15:00-15:59' => '15:00-15:59',
            '16:00-16:59' => '16:00-16:59',
            '17:00-17:59' => '17:00-17:59',
            '18:00-18:59' => '18:00-18:59',
            '19:00-19:59' => '19:00-19:59',
            '20:00-20:59' => '20:00-20:59',
            '21:00-21:59' => '21:00-21:59',
            '22:00-22:59' => '22:00-22:59',
            '23:00-23:59' => '23:00-23:59',
        ];
    }
}