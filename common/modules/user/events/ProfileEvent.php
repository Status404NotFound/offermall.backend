<?php


namespace common\modules\user\events;

use common\modules\user\models\tables\BaseProfile;
use yii\base\Event;

/**
 * @property BaseProfile $model
 *
 * @author makandy <makandy42@gmail.com>
 */
class ProfileEvent extends Event
{
    /**
     * @var BaseProfile
     */
    private $_profile;

    /**
     * @return BaseProfile
     */
    public function getProfile()
    {
        return $this->_profile;
    }

    /**
     * @param BaseProfile $form
     */
    public function setProfile(BaseProfile $form)
    {
        $this->_profile = $form;
    }
}
