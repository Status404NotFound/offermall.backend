<?php

namespace common\services\log\logs;

use common\helpers\FishHelper;
use common\models\log\Logs;
use common\services\ValidateException;
use yii\db\ActiveRecord;
use Yii;

class LogsService
{
    private $log;

    public function logModel(ActiveRecord $model, $oldModel = null, $onDelete = false)
    {
        $modelName = $model->tableName();

        if ($modelName == 'logs' || $modelName == 'login_log' || $modelName == 'order_sku_log' || $modelName == 'order_info_log'
            || ($modelName == 'order')
            || ($modelName == 'order_data')
            || ($modelName == 'customer')
            || ($modelName == 'customer_system')
        ) return true;

        $this->log = new Logs();
        $this->log->ip = $_SERVER['REMOTE_ADDR'];
        $this->log->model = $modelName;
        $this->log->datetime = date('Y-m-d H:i:s');

        $this->log->new_data = clone $model;
        if (isset($this->log->new_data->img)) $this->log->new_data->img = ''; // Because BLOB

        if ($model->isNewRecord) {
            $this->log->last_data = [];
            $this->log->comment = $this->getInsertComment($model);
        } else {
            $this->log->last_data = clone $oldModel;
            if (isset($this->log->last_data->img)) $this->log->last_data->img = ''; // Because BLOB
            $this->log->comment = $this->getUpdateComment($model, $oldModel);
        }

        if ($modelName == 'order') {
            foreach ($this->log->last_data->attributes as $name => $value) {
                if (is_float($this->log->last_data->$name)) {
                    $this->log->new_data->$name = round($this->log->new_data->$name, 3);
                    $this->log->last_data->$name = round($this->log->last_data->$name, 3);
                }
                if (is_integer($this->log->last_data->$name)) $this->log->new_data->$name = (int)$this->log->new_data->$name;
            }
        }

        //ничего не поменялось
        if (isset($this->log->last_data->attributes) && ($this->log->last_data->attributes == $this->log->new_data->attributes))
            return true;

        $this->log->new_data = serialize($this->log->new_data->attributes);
        $this->log->last_data = (!empty($this->log->last_data) && $this->log->last_data instanceof ActiveRecord) ?
            serialize($this->log->last_data->attributes) : null;

        /* Тут можем проверить имеет ли право что-то менять данный юзер
        if(Yii::app()->user->isGuest)
        if($modelName == 'someName')
        if(Yii::app()->user->id == 25)
        и тп
        Если у пользователя нет прав, то не дадим это сохранить
           return false;
        */

        if (Yii::$app->user->isGuest) {
            $this->log->user_id = NULL;
        } else {
            $this->log->user_id = Yii::$app->user->identity->getId();
        }
        if (!$this->log->save())
            FishHelper::debug($this->log->errors, 1, 1);
//            throw new ValidateException($this->log->errors);
        return true;
    }

    private function getInsertComment(ActiveRecord $model)
    {
        $comment = $this->getBaseComment() . ' add ' . $model::className() . ', set ';
        foreach ($model->attributes as $name => $value) {
            $comment .= $name . '=' . $value . '; ';
        }
        return $comment;
    }

    private function getUpdateComment(ActiveRecord $model, ActiveRecord $oldModel)
    {
        $comment = $this->getBaseComment() . ' change ' . $model::className() . ' ';
        foreach ($model->attributes as $name => $value) {
            if ($oldModel->$name != $value)
                $comment .= $name . ' from ' . $oldModel->$name . ' to ' . $value . '; ';
        }
        return $comment;
    }

    private function getBaseComment()
    {
        $username = isset(Yii::$app->user->identity->username) ? Yii::$app->user->identity->username : '';
        return $this->log->datetime . ' | ' . $this->log->ip . ': ' . $username;
    }
}