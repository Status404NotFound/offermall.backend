<?php
namespace crm\services\notify;

use Yii;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\timezone\TimeZoneSrv;

class NotifyService
{
    private $file_1 = 'you-wouldnt-believe.mp3';
    private $file_2 = 'job-done.mp3';
    private $file_3 = 'croak.mp3';

    /**
     * @return array
     */
    public function getListOfNotificationAudio()
    {
        $my_class = new NotifyService();
        $class_vars = get_class_vars(get_class($my_class));

        $audio = [];
        foreach ($class_vars as $name => $value) {

            $file_name = 'http://' . $_SERVER['HTTP_HOST'] . '/notify/'. $value;
            $audio_data = base64_encode(file_get_contents($file_name));
            $src = 'data:audio/mpeg;base64,' . $audio_data;
            $audio_name = str_replace('.mp3', '', $value);

            $audio[] = [
                'audio_name' => $audio_name,
                'audio_data' => $src,
            ];
        }

        return $audio;
    }
}