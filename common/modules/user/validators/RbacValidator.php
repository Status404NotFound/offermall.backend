<?php

namespace common\modules\user\validators;

use common\modules\user\traits\AuthManagerTrait;
use yii\validators\Validator;

/**
 * @author makandy <makandy42@gmail.com>
 */
class RbacValidator extends Validator {
    use AuthManagerTrait;

    
    /** @inheritdoc */
    protected function validateValue($value)
    {
        $authManager = $this->getAuthManager();
        if (is_string($value)) {
            if ($authManager->getItem($value) == null) {
                return [\Yii::t('user', 'There is neither role nor permission with name "{0}"', [$value]), []];
            }
            return null;
        }
        if (is_array($value)) {
            foreach ($value as $val) {
                if ($authManager->getItem($val) == null) {
                    return [\Yii::t('user', 'There is neither role nor permission with name "{0}"', [$val]), []];
                }
            }
        }

        return [print_r([$value, $authManager->getItem($value[0])],true), []];
    }
}