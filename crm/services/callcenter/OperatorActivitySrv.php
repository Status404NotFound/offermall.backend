<?php
namespace crm\services\callcenter;

use common\models\callcenter\OperatorDaily;
use yii\helpers\ArrayHelper;

class OperatorActivitySrv
{
    public $list;
    
    public function __construct($params)
    {
        $this->list = $this->getActivity($params);
    }
    
    protected function getActivity($params): array
    {
        $operator_daily_query = OperatorDaily::find()
            ->select(['operator_daily.*', 'user.username as operator_name',])
            ->join('LEFT JOIN', 'user', 'user.id = operator_daily.operator_id')
            ->join('LEFT JOIN', 'user_child', 'user_child.child = operator_id');
        $filters = $params['filters'];
        
        if ( !empty($filters['time'])) {
            $start = new \DateTime($filters['time']['start']);
            $end = new \DateTime($filters['time']['end']);
        } else {
            $start = new \DateTime();
            $start->setDate(date('Y'), date('m'), 1);
            $end = clone $start;
            $end->setDate(date('Y'), date('m'), date('t'));
        }
        
        $start->setTime(0, 0, 0);
        $start_date = $start->format('Y-m-d H:i:s');
        
        $end->setTime(23, 59, 59);
        $end_date = $end->format('Y-m-d H:i:s');
        $operator_daily_query->andWhere(['between', 'operator_daily.date', $start_date, $end_date]);
        
        if ( !empty($filters['operator_id'])) {
            $operator_daily_query->andWhere(['operator_daily.operator_id' => $filters['operator_id']['value']]);
        }
        
        if ($owner_id = \Yii::$app->user->identity->getOwnerId()) {
            $operator_daily_query->andWhere(['user_child.parent' => $owner_id]);
        }
        
        $activity = [];
        
        foreach ($operator_daily_query->asArray()->all() as $record) {
            $day = date('d', strtotime($record['date']));
            $active_time['day_' . $day] = $record['active_time'];
            
            if ( !isset($activity[$record['operator_id']])) {
                $activity[$record['operator_id']] = [
                    'operator_id'         => $record['operator_id'],
                    'operator_name'       => $record['operator_name'],
                    'total_active_time'   => $record['active_time'],
                    'total_inactive_time' => $record['inactive_time'],
                    'activity_by_day'     => $active_time
                ];
            } else {
                $activity[$record['operator_id']]['activity_by_day'] += $active_time;
                $activity[$record['operator_id']]['total_active_time'] += $record['active_time'];
                $activity[$record['operator_id']]['total_inactive_time'] += $record['inactive_time'];
            }
            unset($active_time);
        }
        
        $sort_field = $params['sortField'] ?? null;
        $sort_order = $params['sortOrder'] ?? null;
        $sort_type = $sort_order === 1 ? [SORT_ASC] : [SORT_DESC];
    
        switch ($sort_field) {
            case 'operator_id':
                ArrayHelper::multisort($activity, ['operator_id'], $sort_type);
            case 'operator_name':
                ArrayHelper::multisort($activity, ['operator_name'], $sort_type);
            default:
                ArrayHelper::multisort($activity, function($item) use ($sort_field) {
                    return $item['activity_by_day']['day_' . $sort_field] ?? null;
                }, $sort_type);
        }
        
        foreach ($activity as $operator_id => &$record) {
            $record['total_active_time'] = $this->convertToHours($record['total_active_time']) ?? '< 0.1';
            $record['total_inactive_time'] = $this->convertToHours($record['total_inactive_time'] ?? '< 0.1');
            
            foreach ($record['activity_by_day'] as $day => $seconds) {
                $record['activity_by_day'][$day] = $this->convertToHours($seconds) ?? '< 0.1';
            }
        }
    
        return array_values($activity);
    }
    
    private function convertToHours($sec): ?string
    {
        if ($sec < 360) {
            return null;
        }
        
        $hours = 0;
        if ($sec >= 3600) {
            $hours = floor($sec / 3600);
            $sec %= 3600;
        }
        
        $minutes = 0;
        if ($sec >= 360) {
            $minutes = floor($sec / 360);
        }
        
        return $hours . ',' . $minutes;
    }
}