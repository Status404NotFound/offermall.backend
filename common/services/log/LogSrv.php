<?php

namespace common\services\log;

use common\helpers\FishHelper;
use common\models\Instrument;
use common\models\order\OrderSku;
use common\modules\user\models\tables\User;
use common\services\ValidateException;
use crm\services\log\LogFactory;
use yii\db\ActiveRecord;
use Yii;

class LogSrv
{
    public $record_table;
    public $log_table;

    public $log;

    public $column;
    public $old_data;

    public $record;
    public $comment;
    public $instrument_id;

    public function __construct(ActiveRecord $record, int $instrument_id, $comment = null)
    {
        $this->record = $record;
        $this->record_table = $record::tableName();
        $this->log_table = $this->getLogTableName();
        $this->instrument_id = $instrument_id;
        $this->comment = $comment;
    }

    public function add($primaryKey = null)
    {
        $changed_attributes = $this->record->getDirtyAttributes();
        foreach ($changed_attributes as $record_column => $changed_attribute) {
            $this->log = new $this->log_table();

            $this->log->user_id = Yii::$app->user->id;
            $this->log->instrument = $this->instrument_id;

            $this->log->comment = $this->comment;

            $this->log->row_id = isset($this->record->primaryKey) ? (integer)$this->record->primaryKey : (integer)$primaryKey;

            $this->log->column = $record_column;
            $oldData = $this->record->getOldAttribute($record_column);
            $this->log->old_data = $oldData !== null ? (string)$oldData : '';

            if (isset($order_id)) $this->log->order_id = $order_id;
            if (isset($sku_id)) $this->log->sku_id = $sku_id;

            if (!$this->log->save())
                throw new ValidateException($this->log->errors);
            return true;
//            if ($this->log->save()) return true;
//            else var_dump($this->log->errors);
//            exit;
        }

        if (empty($changed_attributes) && $this->comment != null) {

            $this->log = new  $this->log_table();

            $this->log->user_id = Yii::$app->user->id;
            $this->log->instrument = $this->instrument_id;

            $this->log->comment = $this->comment;
            $this->log->row_id = $this->record->primaryKey;

            $this->log->column = $this->column;
            $this->log->old_data = $this->old_data;

            if ($this->log->save()) return true;
            else var_dump($this->log->errors);
            exit;
        }

        return false;
    }

    private function getLogTableName()
    {
        $logTableNamespace = 'common\\models\\log\\';
        $name = '';
        foreach (explode('_', $this->record_table) as $part) {
            $name .= ucfirst($part);
        }
        $name .= 'Log';
        return $logTableNamespace . $name;
    }

    public function getComments($instruments = [])
    {

    }

    public function getComment()
    {
        $user = User::find()->select('username')->where(['id' => $this->log->user_id])->asArray()->one();
        $comment = isset($this->log->datetime) ? $this->log->datetime : date('Y-m-d H:i:s') . ": User " . ucfirst($user['username']) . Instrument::getAction($this->log->instrument) . $this->log->comment;
        return ['comment' => $comment, 'color' => Instrument::instrumentCommentColor()[$this->log->instrument]];
    }
}