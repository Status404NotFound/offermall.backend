<?php
namespace common\components\ccApi\requests;

use yii;
use common\components\ccApi\core\CcApi;

/**
 * Class OrderHistory
 * @package app\models\ccApi\requests
 */
class OrderHistory extends CcApi
{
    protected $api_key = 4;

    public $order_id;
    public $group_by_call;


    const STATUS_CALL = 'CALL';                                //- инициилизация звонка
    const STATUS_CLIENT_NOANSWER = 'CLIENT_NOANSWER';          //- клиент не взял трубку
    const STATUS_CLIENT_BUSY = 'CLIENT_BUSY';                  //- клиент сбросил
    const STATUS_CLIENT_CANCEL = 'CLIENT_CANCEL';              //- клиент положил трубку до связи с оператором
    const STATUS_CLIENT_PICKUP = 'CLIENT_PICKUP';              //- клиент снял трубку
    const STATUS_CLIENT_CALLTO = 'CLIENT_CALLTO';              //- звонок клиенту
    const STATUS_OPERATOR_NOANSWER = 'OPERATOR_NOANSWER';      //- оператор не взял трубку
    const STATUS_OPERATOR_BUSY = 'OPERATOR_BUSY';              // - оператор сбросил звонок
    const STATUS_OPERATOR_PICKUP = 'OPERATOR_PICKUP';          //- оператор взял трубку
    const STATUS_OPERATOR_CALLTO = 'OPERATOR_CALLTO';          //- звонок оператору
    const STATUS_OPERATOR_OFFLINE = 'OPERATOR_OFFLINE';        //- оператор на которого пошел дозвон сейчас оффлайн.
    const STATUS_ERROR = 'ERROR';                              //- ошибка в процессе. Например экстенш в этом направлении отсутствует.
    const STATUS_FINISH = 'FINISH';                            //- успешное окончание звонка (приходит если клиент был соеденен с оператором, и после этого было уже окончание разговора)


    public function init()
    {
        parent::init();
    }

    public function rules()
    {
        return [
            [['order_id', 'group_by_call'], 'safe']
        ];
    }

}
