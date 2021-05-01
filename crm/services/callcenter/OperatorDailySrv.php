<?php
/**
 * Created by PhpStorm.
 * User: evild
 * Date: 5/15/18
 * Time: 2:14 PM
 */

namespace crm\services\callcenter;

use common\models\callcenter\OperatorActivity;
use common\models\callcenter\OperatorDaily;
use common\services\cache\CacheCommonSrv;
use common\services\webmaster\ArrayHelper;
use yii\base\Model;

class OperatorDailySrv extends Model
{
    public function addDailyOperatorActivity()
    {
        $cacheSrv = new CacheCommonSrv();
        $cache_data = $cacheSrv->getExecutedRecordsByKeyPart('table:operator_activity;');
        
        if (empty($cache_data)) {
            
            return false;
        }
        
        $operators_activity = [];
        foreach ($cache_data as $record) {
            $operators_activity[$record['operator_id']][strtotime($record['datetime'])] = $record['action'];
        }
        
        $activity = $this->countOperatorActivity($operators_activity);
        
        foreach ($activity as $operator_id => $day_activity) {
            $dates = array_keys($day_activity);
            
            foreach ($dates as $date) {
                $daily_activity = OperatorDaily::findOne(['date' => $date, 'operator_id' => $operator_id]);
                
                if ( !is_null($daily_activity)) {
                    $daily_activity->active_time += $day_activity[$date]['active_time'];
                    $daily_activity->inactive_time += $day_activity[$date]['inactive_time'];
                    $daily_activity->update();
                } else {
                    $model = new OperatorDaily();
                    $model->operator_id = $operator_id;
                    $model->date = $date;
                    $model->active_time = $day_activity[$date]['active_time'];
                    $model->inactive_time = $day_activity[$date]['inactive_time'];
                    $model->save();
                }
            }
        }
        
        $cacheSrv->flushRecordsByKeyPart('table:operator_activity;');
    }
    
    private function countOperatorActivity(array $operators_activity): array
    {
        $activity = [];
        
        foreach ($operators_activity as $operator_id => $records) {
            krsort($records);
            
            if ( !empty($prev_user) && $prev_user != $operator_id) {
                unset($prev_timestamp_date);
                unset($prev_action);
            }
            
            foreach ($records as $timestamp_date => $action) {
                $date = date('Y-m-d', $timestamp_date);
                
                if ( !isset($activity[$operator_id][$date])) {
                    $activity[$operator_id][$date] = ['active_time' => 0, 'inactive_time' => 0];
                }
                
                if ( !empty($prev_timestamp_date[$date]) && !empty($prev_action[$date])) {
                    
                    if ($prev_action[$date] == OperatorActivity::ACTION_BREAK
                        || $prev_action[$date] == OperatorActivity::ACTION_NOT_ACTIVE_MOUSE
                        || $prev_action[$date] == OperatorActivity::ACTION_END_OF_SHIFT) {
                        $activity[$operator_id][$date]['inactive_time'] += $prev_timestamp_date[$date] - $timestamp_date;
                    }
                    if ($prev_action[$date] == OperatorActivity::ACTION_START_OF_SHIFT
                        || $prev_action[$date] == OperatorActivity::ACTION_OPEN_WINDOW) {
                        $activity[$operator_id][$date]['active_time'] += $prev_timestamp_date[$date] - $timestamp_date;
                    }
                }
                
                $prev_timestamp_date[$date] = $timestamp_date;
                $prev_action[$date] = $action;
            }
            $prev_user = $operator_id;
        }
        
        return $activity;
    }
}