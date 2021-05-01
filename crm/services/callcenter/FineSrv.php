<?php
namespace crm\services\callcenter;

use common\helpers\FishHelper;
use common\models\callcenter\OperatorFine;
use common\models\Instrument;
use common\services\log\LogSrv;
use yii\helpers\ArrayHelper;

class FineSrv
{
    public function search($params = [])
    {
        $owner_id = \Yii::$app->user->identity->getOwnerId();
        
        $query = OperatorFine::find()
                             ->select('operator_fine.*, user.username as operator_name')
                             ->join('INNER JOIN', 'user', 'user.id = operator_fine.operator_id')
                             ->join('LEFT JOIN', 'user_child', 'user_child.child = operator_fine.operator_id');
        
        if ( !is_null($owner_id)) {
            $query->andWhere(['user_child.parent' => $owner_id]);
        }
        
        return $query;
    }
    
    public function fines()
    {
        $fines = $this->search()->asArray()->all();
        $total_fines = [];
        
        foreach ($fines as $key => $fine) {
            $fines[$key]['statuses'] = OperatorFine::getStatus();
            $total_fines[$fine['operator_id']]['operator_id'] = $fine['operator_id'];
            $total_fines[$fine['operator_id']]['operator_name'] = $fine['operator_name'];
            
            if (isset($total_fines[$fine['operator_id']]['statuses'][$fine['status_id']])) {
                $total_fines[$fine['operator_id']]['statuses'][$fine['status_id']] += 1;
            } else {
                $total_fines[$fine['operator_id']]['statuses'][$fine['status_id']] = 1;
            }
        }
        
        foreach ($total_fines as $operator_id => $operator) {
            $fines_string = '';
    
            if (isset($operator['statuses'][OperatorFine::STATUS_PENDIG])) {
                $fines_string .= OperatorFine::getStatus()[OperatorFine::STATUS_PENDIG] . ": " . $operator['statuses'][OperatorFine::STATUS_PENDIG];
            }
            
            if (isset($operator['statuses'][OperatorFine::STATUS_APPROVE])) {
                if ( !empty($fines_string)) $fines_string .= ', ';
                $fines_string .= OperatorFine::getStatus()[OperatorFine::STATUS_APPROVE] . ": " . $operator['statuses'][OperatorFine::STATUS_APPROVE];
            }
            
            if (isset($operator['statuses'][OperatorFine::STATUS_REJECT])) {
                if ( !empty($fines_string)) $fines_string .= ', ';
                $fines_string .= OperatorFine::getStatus()[OperatorFine::STATUS_REJECT] . ": " . $operator['statuses'][OperatorFine::STATUS_REJECT];
            }
            
            if (isset($operator['statuses'][OperatorFine::STATUS_PAID])) {
                if ( !empty($fines_string)) $fines_string .= ', ';
                $fines_string .= OperatorFine::getStatus()[OperatorFine::STATUS_PAID] . ": " . $operator['statuses'][OperatorFine::STATUS_PAID];
            }
            $total_fines[$operator_id]['statuses'] = $fines_string;
        }

        return [
            'fines' => $fines,
            'total' => array_values($total_fines)
        ];
    }
    
    public function changeFineStatus($fine_id, $fine_status = OperatorFine::STATUS_PENDIG)
    {
        $fine = OperatorFine::findOne($fine_id);
        $fine->status_id = $fine_status;
        
        if ($fine->update(['status_id'])) {
            //            $log = new LogSrv($fine, Instrument::LMC_CALL_CENTER_FINE);
            //            $log->add();
            return true;
        }
        
        return false;
    }
}