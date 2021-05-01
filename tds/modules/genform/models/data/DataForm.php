<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 07.02.17
 * Time: 1:10
 */

namespace tds\modules\genform\models\data;


use common\helpers\FishHelper;
use common\models\offer\Offer;
use tds\modules\genform\models\data\extension\DataExtension;
use tds\modules\genform\models\data\field\EncodeDecodeDataInterface;
use tds\modules\genform\models\data\hidden\DataHidden;
use tds\modules\genform\models\data\pages\DataPages;
use tds\modules\genform\models\builder\theme\BootstrapTheme;
use tds\modules\genform\models\builder\theme\Theme;
use tds\modules\genform\models\forms\Production;
use tds\modules\genform\models\pages\PageForm;
use tds\modules\genform\tables\GenFormTable;
use \Error;

class DataForm implements DataFormInterface
{
    /**
     * @var GenFormTable
     */
    private $modelDataTable;
    /**
     * Массив данных скрытых полей.
     *
     * @var array
     */
    private $arrHidden = [];

    /**
     * Массив данных страниц формы.
     *
     * @var array
     */
    private $arrPages = [];

    /**
     * Массив данных расширений формы.
     *
     * @var array
     */
    private $arrExtension = [];

    /**
     * Объект работающий со скрытыми полями.
     *
     * @var DataHidden
     */
    private $dataHidden;

    /**
     * Объект работабщий со страницами формы.
     *
     * @var DataPages
     */
    private $dataPages;

    /**
     * Объект работабщий с расширениями формы.
     *
     * @var DataExtension
     */
    private $dataExtension;

    private $hash = 123;
    private $theme;
    private $nameForm = 'Default form';
    private $userId;
    private $offer_id;
    private $formId;


    public function __construct($idForm = null, $hash = null, $nameForm = null, $offer_id = null)
    {
        $this->dataHidden = new DataHidden();
        $this->dataPages = new DataPages();
        $this->dataExtension = new DataExtension();

        //Transfer data pointer of array
        $this->dataHidden->setData($this->arrHidden);
        $this->dataPages->setData($this->arrPages);
        $this->dataExtension->setData($this->arrExtension);
        if (is_scalar($hash)) {
            $this->setHash($hash);
        }
        if (is_string($nameForm)) {
            $this->setNameForm($nameForm);
        }

        $this->offer_id = $offer_id;

        if (is_null($this->userId = \Yii::$app->user->identity->getId())) {
            throw new Error('For this user permission denied');
        }

        if ($idForm > 0) {
            $this->uploadData($idForm);
        } elseif (is_null($idForm)) {
            $this->createData();
        } else {
            throw new Error("Wrong ID ({$idForm}) form!");
        }
    }

    public function getOfferHash()
    {
        $offer = Offer::findOne($this->offer_id);

        $offer_hash = null;
        if(!empty($offer)) $offer_hash = $offer->offer_hash;

        return $offer_hash;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        $hash = $this->hash == 123 ?
            rand(0, 500) :
            $this->hash;
        $this->hash = $hash;
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * Возвращаем инициализованый объект для работы со скрытыми полями.
     *
     * @return DataHidden
     */
    public function getHidden()
    {
        return $this->dataHidden;
    }

    /**
     * Возвращаем инициализованый объект для работы со страницами формы.
     *
     * @return DataPages
     */
    public function getPages()
    {
        return $this->dataPages;
    }

    /**
     * Возвращаем инициализованый объект для работы с расширениями формы.
     *
     * @return DataExtension
     */
    public function getExtension()
    {
        return $this->dataExtension;
    }

    /**
     * Возврат массива данных для скрытых полей.
     *
     * @return array
     */
    public function getArrHidden()
    {
        return $this->arrHidden;
    }

    /**
     * Установка массива данных для скрытых полей.
     *
     * @param array $arrHidden
     */
    public function setArrHidden(array $arrHidden)
    {
        $this->arrHidden = $arrHidden;
    }

    /**
     * Возврат массива данных для страниц формы.
     *
     * @return array
     */
    public function getArrPages()
    {
        return $this->arrPages;
    }

    /**
     * Установка массива данных для страниц формы.
     *
     * @param array $arrPages
     */
    public function setArrPages(array $arrPages)
    {
        $this->arrPages = $arrPages;
    }

    /**
     * Возврат массива данных для расширений формы.
     *
     * @return array
     */
    public function getArrExtension()
    {
        return $this->arrExtension;
    }

    /**
     * Установка массива данных для расширений формы.
     *
     * @param array $arrExtension
     */
    public function setArrExtension(array $arrExtension)
    {
        $this->arrExtension = $arrExtension;
    }

    private function uploadData($idForm)
    {
        if (is_null($this->modelDataTable = GenFormTable::find()->where([
            'id' => $idForm,
            'user_id' => $this->getUserId()
        ])->one())) {
            //throw new Error('The form does not exist or you have no permissions to get it!');
        } else {
            $this->modelDataTable = GenFormTable::findOne($idForm);
            //Load data into arrays
            $this->arrHidden = $this->decode(json_decode($this->modelDataTable->hidden_conf, true));
            $this->arrPages = $this->decode(json_decode($this->modelDataTable->pages_conf, true));
            $this->arrExtension = $this->decode(json_decode($this->modelDataTable->extensions, true));

            $this->theme = $this->modelDataTable->theme;
            $this->nameForm = $this->modelDataTable->name;
            $this->hash = $this->modelDataTable->hash;

            //Transfer data pointer of array
            $this->dataHidden->setData($this->arrHidden);
            $this->dataPages->setData($this->arrPages);
            $this->dataExtension->setData($this->arrExtension);
        }
    }

    /**
     * @return int|string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    public function getOfferId()
    {
        return $this->offer_id;
    }

    private function createData()
    {
        $this->modelDataTable = new GenFormTable();
        $theme = new BootstrapTheme();

        $this->modelDataTable->name = $this->getNameForm();
        $this->modelDataTable->user_id = $this->getUserId();
        $this->modelDataTable->offer_id = $this->getOfferId();
        $this->modelDataTable->theme = $theme::getNameTheme();

        $this->testData($theme);
        $this->saveData();
    }

    public function testData(Theme $theme)
    {
//        $this->dataHidden->addField($theme, 'ip');
        $this->dataHidden->addField($theme, 'referrer');
        $this->dataHidden->addField($theme, 'view_time');
        $this->dataHidden->addField($theme, 'cookie', null, ['class' => 'adfsh-ck']);
        $this->dataHidden->addField($theme, 'view_hash', null, ['class' => 'orderViewHash']);
        $this->dataHidden->addField($theme, 'sid', 'sid_form');
        $this->dataHidden->addField($theme, 'offer_hash', $this->getOfferHash());


        $this->dataPages->addField('form', 'header', $theme, Theme::TYPE_HEADER_TITLE)
            ->addParam('title', 'Name Offer');
        $this->dataPages->addField('form', 'content', $theme, Theme::TYPE_TEXT_INPUT);
        $this->dataPages->addField('form', 'content', $theme, Theme::TYPE_TELEPHONE);
        $this->dataPages->addField('form', 'content', $theme, Theme::TYPE_TEXTAREA);
        $this->dataPages->addField('form', 'footer', $theme, Theme::TYPE_BUTTON_SUBMIT);

        $this->dataPages->addField('ThanksPages', 'header', $theme, Theme::TYPE_HEADER_TITLE)
            ->addParam('title', 'Thanks Pages');
        $this->dataPages->addField('ThanksPages', 'content', $theme, Theme::TYPE_TEXTAREA);
        $this->dataPages->addField('ThanksPages', 'footer', $theme, Theme::TYPE_BUTTON);
    }

    /**
     * @throws Error
     */
    public function saveData()
    {
        $this->modelDataTable->hidden_conf = json_encode($this->encode($this->arrHidden));
        $this->modelDataTable->pages_conf = json_encode($this->encode($this->arrPages));
        $this->modelDataTable->extensions = json_encode($this->encode($this->arrExtension));
        $this->modelDataTable->hash = (string)$this->getHash();

        if ($this->modelDataTable->validate() && $this->modelDataTable->save()) {
            $this->formId = $this->modelDataTable->id;

            /**
             * gen or update files
             */

            $modelTheme = new BootstrapTheme();
            $modelPages = new PageForm();
            $modelForm = new Production($this, $modelPages, $modelTheme);


            $fileGenerator = new FileGenerator($this->modelDataTable->id);

            $fileGenerator->genFile($modelForm->getCSS(), 'css');
            $fileGenerator->genFile($modelForm->getHTML(), 'html');
            //$fileGenerator->genFile($modelForm->getJS(), 'js');
        } else {
            throw new Error("Unable to save the form! Error: " . implode('. ', $this->modelDataTable->errors));
        }
    }

    /**
     * @param EncodeDecodeDataInterface|mixed $data
     * @return EncodeDecodeDataInterface|mixed
     * @throws Error
     */
    private function encode($data)
    {
        if (is_array($data)) {
            return array_map(array($this, 'encode'), $data);
        } elseif (is_scalar($data)) {
            return $data;
        } elseif (is_object($data) && method_exists($data, 'encodeDataToArray')) {
            return $data->encodeDataToArray();
        } else {
            throw new Error("Encode data in class " . get_class($this) . " not valid. Value: " . print_r($data, true));
        }
    }

    /**
     * @param $arr
     * @return EncodeDecodeDataInterface|array
     * @throws Error
     */
    private function decode(array $arr)
    {
        if (isset($arr['className'])) {
            /** @var EncodeDecodeDataInterface $object */
            $object = new $arr['className']();
            $object->decodeArrayToData($arr);
            return $object;
        } else {
            return array_map(array($this, 'decode'), $arr);
        }
    }

    /**
     * @return string
     */
    public function getNameForm()
    {
        return $this->nameForm;
    }

    /**
     * @param string $nameForm
     */
    public function setNameForm($nameForm)
    {
        $this->nameForm = $nameForm;
    }

    /**
     * @return mixed
     */
    public function getFormId()
    {
        return $this->formId;
    }

}
