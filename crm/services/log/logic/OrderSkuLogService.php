<?php

namespace crm\services\log\logic;

use common\helpers\FishHelper;
use common\models\log\OrderSkuLog;
use common\models\order\OrderSku;
use common\services\log\LogServiceException;
use crm\services\log\LogInterface;
use yii\db\ActiveRecord;
use Yii;

class OrderSkuLogService implements LogInterface
{
    /** @param OrderSku $orderSku */
//    private $orderSku;
//    private $changedData;
//    private $logModel;
//    private $comment;
//    private $instrument;
//    private $pk;

//    public function compareData(ActiveRecord $model, $primaryKey = null)
//    {
//        $this->orderSku = $model;
//        $this->pk = $primaryKey;
//        $this->changedData = $model->getDirtyAttributes();
//
//        FishHelper::debug($this->changedData, 0, 0);
//
//        if (empty($this->changedData)) $this->changedData['amount'] = 0;
//
//
//////        FishHelper::debug((int)$this->changedData['amount'] == (int)$this->orderSku->getOldAttribute('amount'), 1, 0);
////        FishHelper::debug($this->changedData['amount'], 1, 0);
////        FishHelper::debug($this->orderSku->getOldAttribute('amount'), 1, 0);
//
//        if (isset($this->changedData['amount']) && (int)$this->changedData['amount'] == (int)$this->orderSku->getOldAttribute('amount'))
//            unset($this->changedData['amount']);
//
//        if (isset($this->changedData['order_id'])) unset($this->changedData['order_id']);
//        if (isset($this->changedData['sku_id'])) unset($this->changedData['sku_id']);
//    }

//    public function saveLog()
//    {
//        foreach ($this->changedData as $attr_name => $attr_value) {
//            $this->logModel = new OrderSkuLog();
//            $this->logModel->row_id = isset($this->orderSku->primaryKey) ? (integer)$this->orderSku->primaryKey : (integer)$this->pk;
//            $this->logModel->column = $attr_name;
//            $this->logModel->user_id = Yii::$app->user->identity->getId();
//            $this->logModel->instrument = !empty($this->instrument) ? $this->instrument : null;
//            $this->logModel->comment = !empty($this->comment) ? $this->comment : '';
//
//            $oldData = $this->orderSku->getOldAttribute($attr_name);
//            $this->logModel->old_data = !empty($oldData) ? (string)$oldData : '';
//
//            $this->logModel->order_id = $this->orderSku->order_id;
//            $this->logModel->sku_id = $this->orderSku->sku_id;
//            if (!$this->logModel->save()) throw new LogServiceException($this->logModel->errors);
//        }
//    }
//
//    public function setComment($comment)
//    {
//        $this->comment = (string)$comment;
//    }
//
//    public function setInstrument($instrument)
//    {
//        $this->instrument = (integer)$instrument;
//    }
}