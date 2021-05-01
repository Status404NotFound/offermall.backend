<?php

namespace tds\modules\genform\models\data;

use tds\modules\genform\models\handlers\OfferHandler;
use Yii;
use yii\base\Exception;

/**
 * Class FileGenerator
 * @package tds\modules\genform\models\data
 *
 * при створенні об'єкту потрібно передати в конструктор id форми
 *
 * для генерації файлу для форми портібно через обєкт викликати функцію genFile() на вхід якої йде вміст файлу та його розширення
 *
 * для генерації скрипта підключення потрібно викликати функцію genMainJsScript()
 */
class FileGenerator implements FormFileSaveInterface
{
    public $form_id;
    public $path;

    public function __construct(int $form_id)
    {
        if (!empty($form_id)) {

            $this->form_id = $form_id;

            if (!is_dir(Yii::$app->basePath . '/web/offer_forms/' . $this->form_id)) {
                try {
                    mkdir(Yii::$app->basePath . '/web/offer_forms/' . $this->form_id);
                } catch (\yii\base\Exception $e) {
                    return $e;
                }
            }

            $this->path = Yii::$app->basePath . '/web/offer_forms/' . $this->form_id . '/';

        } else {
            throw new Exception('No offer selected!');
        }
    }

    public function genFile($fileSource, string $fileExtension)
    {
        if (!empty($fileSource)) {
            $fp = fopen($this->path . 'form.' . $fileExtension, "w");
            fwrite($fp, $fileSource);
            fclose($fp);
//            file_put_contents($this->path . 'form.' . $fileExtension, $fileSource);
            return true;
        } else {
            throw new Exception('No source code for writing!');
        }
    }


    public function genMainJsScript()
    {
        $js = '<script type="text/javascript" src="//' . Yii::$app->params['tds_url'] . '/form-handler.js"></script>' . PHP_EOL;
        $js .= '<script type="text/javascript" src="//' . Yii::$app->params['tds_url'] . '/getFileContent.js"></script>' . PHP_EOL;
        $js .= '<script type="text/javascript">' . PHP_EOL;
        $js .= "getContent(" . $this->form_id . ", 'html');" . PHP_EOL;
//        $js .= 'document.getElementById(\'form-wrapper\').innerHTML += getContent(' . $this->form_id . ', \'html\');' . PHP_EOL;
        $js .= "window.onload = function(){ initForm(); }" . PHP_EOL;
        $js .= 'addCss(\'//' . Yii::$app->params['tds_url'] . '/offer_forms/' . $this->form_id . '/form.css\');' . PHP_EOL;
        $js .= '</script>' . PHP_EOL;

        //$js .= '<script type="text/javascript" src="//tds.advertfish.com/offer_forms/' . $this->form_id . '/form.js"></script>';

        return $js;
    }

//    public static function deleteFiles()
//    {
//
//    }
}

