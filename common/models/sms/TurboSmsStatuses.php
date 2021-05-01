<?php
namespace common\models\sms;

use Yii;
use yii\base\Model;

class TurboSmsStatuses extends Model
{
    public $sms_id;
    public $message_id;
    private $_status;

    /**
     * @return array
     */
    public function smsStatuses()
    {
        return [
            0 => 'Сообщение отправлено в тестовом режиме', //Запись в локальной базу существует, но message_id для запроса отсутствует
            1 => 'Сообщение с ID X не найдено', //ID сообщения отсутствует в базе
            2 => 'Не достаточно параметров для выполнения функции', //Не были переданы все необходимые параметры
            3 => 'Вы не авторизированы', //Метод был вызван без вызова метода Auth; Метод Auth не вернул успешную авторизацию; Потеряна сессия авторизации
            4 => 'Отправлено', //У сообщения ещё не обновлялся статус доставки
            5 => 'В очереди', //Сообщение ещё не отправлено
            6 => 'Сообщение передано в мобильную сеть', //Сообщение доставлено на сервер оператора
            7 => 'Сообщение доставлено получателю',
            8 => 'Истек срок сообщения', //	Статус доставки не был получен некоторое время, сообщение потерялось
            9 => 'Удалено оператором', //Сообщение нарушает какие-то правила оператора
            10 => 'Не доставлено',
            11 => 'Сообщение доставлено на сервер', //Сообщение на сервере отправки
            12 => 'Отклонено оператором', //Сообщение нарушает какие-то правила оператора или невозможно доставить смс абоненту
            13 => 'Неизвестный статус', //Свяжитесь с техническим отделом для большей информации
            14 => 'Ошибка, сообщение не отправлено', //Отправка сообщения закончилась неудачей, свяжитесь с техническим отделом для большей информации
            15 => 'Не достаточно кредитов на счете', //Сообщение не отправлено, пополните свой внутренний счёт
            16 => 'Отправка отменена', //Отправка сообщения была отозвана пользователем или администратором
            17 => 'Отправка приостановлена', //Отправка сообщения была прервана пользователем или администратором
            18 => 'Удалено пользователем', //Пользователь или администратор удалил данное сообщение
        ];
    }

    /**
     * @return array
     */
    public static function statusLabels()
    {
        return [
            Yii::t('app', 'Message was sent in test mode'),

            Yii::t('app', 'Message with ID X is not found'),
            Yii::t('app', 'Not enough parameters to execute the request'),
            Yii::t('app', 'You are not authorized'),
            Yii::t('app', 'Message is sent'),
            Yii::t('app', 'Queuing'),
            Yii::t('app', 'The message sent to the mobile network'),
            Yii::t('app', 'The message delivered to the recipient'),
            Yii::t('app', 'Outdated posts'),
            Yii::t('app', 'Removed by operator'),
            Yii::t('app', 'Not delivered'),
            Yii::t('app', 'The message is delivered to the server'),
            Yii::t('app', 'Rejected by the operator'),
            Yii::t('app', 'Unknown status'),
            Yii::t('app', 'The error, message was not sent'),
            Yii::t('app', 'It is not enough credit on the account'),
            Yii::t('app', 'Sending canceled'),
            Yii::t('app', 'Sending suspended'),
            Yii::t('app', 'Deleted by user'),
        ];
    }

    /**
     * Statuses that can be changed manually
     * @param $status
     * @return bool
     */
    public static function isPendingStatuses($status)
    {
        if ($status === null) return true;
        return in_array($status, [4, 5, 6, 11, 15, 17]);
    }

    /**
     * @param $status
     * @return bool|int|string
     */
    public static function statusSuccessRate($status)
    {
        $rates = [
            'success' => [7],
            'warning' => [0, 4, 5, 6, 11, 15, 16, 17, 18],
            'danger' => [1, 2, 3, 8, 9, 10, 12, 13, 14]
        ];
        foreach ($rates as $rate => $values) {
            if (in_array($status, $values)) return $rate;
        }
        return false;
    }

    /**
     * @return int|mixed|null
     */
    public function getStatus()
    {
        if (!$this->_status)
            $this->_status = $this->_requestStatus();
        return $this->_status;
    }

    /**
     * @param $status
     * @return mixed
     */
    public static function statusLabel($status)
    {
        if ($status === null) $status = 4;
        return self::statusLabels()[$status];
    }

    /**
     * @return null
     */
    public function getStatusLabel()
    {
        if ($this->_status)
            return self::statusLabels()[$this->_status];
        return null;
    }

    /**
     * @return int|mixed|null
     */
    private function _requestStatus()
    {
        if (!empty($this->sms_id) && empty($this->message_id))
            return 0; //Zero status with label self::statusLabels()[0];
        $status = array_search(Yii::$app->turbosms->getMessageStatus($this->message_id), $this->smsStatuses());
        return $status !== false ? $status : null;
    }
}