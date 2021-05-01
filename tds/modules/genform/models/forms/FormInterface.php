<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 24.01.17
 * Time: 12:46
 */

namespace tds\modules\genform\models\forms;


interface FormInterface
{
    /**
     * Получить структкру HTML формы.
     *
     * @return string
     */
    public function getHTML();

    /**
     * Получить стили для формы.
     *
     * @return mixed
     */
    public function getCSS();

    /**
     * Получить скрипты для формы.
     *
     * @return mixed
     */
    public function getJS();
}