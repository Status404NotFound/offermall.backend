<?php
namespace webmaster\services\notification;

use Yii;

class TelegramNotification
{
    public function sendNewUser($user, $wmProfile){
        $data = [
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'notificationType' => 'new_user',
            'telegram' => $wmProfile->telegram,
            'skype' => $wmProfile->skype,
        ];
        $this->sendToBot($data);
    }

    public function sendNewPaymentOrder($payment){
        $data = [
            'wm_id' => Yii::$app->user->identity->getId(),
            'amount' => $payment['amount'],
            'comment' => $payment['comment'],
            'notificationType' => 'payment_order'
        ];
        $this->sendToBot($data);
    }

    private function sendToBot($data){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://bot.shopiums.net/notification.php");
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return curl_exec($ch);
    }
}