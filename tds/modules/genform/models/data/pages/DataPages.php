<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 07.02.17
 * Time: 9:07
 */

namespace tds\modules\genform\models\data\pages;


use common\helpers\FishHelper;
use tds\modules\genform\models\data\field\DataField;
use tds\modules\genform\models\data\HandlerDataInterface;
use tds\modules\genform\models\builder\theme\ThemeInterface;

class DataPages implements DataPagesInterface, HandlerDataInterface
{
    private $data = [];

    /**
     * Возврат массива с ключами масива данных, ключи соответствуют именам страниц формы.
     *
     * @return array
     */
    public function getNamePages()
    {
        return array_keys($this->data);
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function getPage($name) {
        if(isset($name)) {
            return $this->data[$name];
        }
        return null;
    }

    /**
     * Возврат массива с данными для создания полей заголовка.
     *
     * @param $namePage
     * @return array
     */
    public function getHeader($namePage)
    {
        return $this->getFields($namePage, 'header');
    }

    /**
     * Возврат массива с данными для создания полей контента.
     *
     * @param $namePage
     * @return array
     */
    public function getContent($namePage)
    {
        return $this->getFields($namePage, 'content');
    }

    /**
     * @param $namePage
     * @param $section
     * @param $idField
     * @return DataField|null
     */
    public function getField($namePage, $section, $idField) {
        $fields = $this->getFields($namePage, $section);

        //Поиск объекта с таким идентификатором.
        $dataFields = array_filter($fields,
            function (DataField $elem) use ($idField) {
                if ( $elem->getOptionsId() === $idField ) {
                    return true;
                }
                return false;
            }
        );

        //Если хоть что то найдено.
        if ( count($dataFields) ) {
            return $this->data[$namePage][$section][array_keys($dataFields)[0]];
        }
        return null;
    }

    public function getArrIdFields($namePage) {
        return array_map(function (DataField $elem) {
            return $elem->getOptionsId();
        }, $this->getContent($namePage));
    }

    public function moveField($namePage, $section, $oldPosition, $newPosition) {
        if ( isset($this->data[$namePage][$section]) ) {
            $data = &$this->data[$namePage][$section];
            if ( count($data) <= 1 ) {
                return false;
            }
            if ( isset($data[$oldPosition]) && isset($data[$newPosition]) ) {
                if ( $oldPosition < $newPosition ) {
                    for ($index = $oldPosition; $index < $newPosition; $index++ ) {
                        list($data[$index], $data[$index+1]) = array($data[$index+1], $data[$index]);
                    }
                } else {
                    for ($index = $oldPosition; $index > $newPosition; $index-- ) {
                        list($data[$index], $data[$index-1]) = array($data[$index-1], $data[$index]);
                    }
                }
            } else {
                throw new \Error('Move the fields of impossible, not valid attribute old or new of position!');
            }
        } else {
            throw new \Error('Name page or section does not exist or not valid attributes!');
        }
    }

    /**
     * Возврат массива с данными для создания полей подвала.
     *
     * @param $namePage
     * @return array
     */
    public function getFooter($namePage)
    {
        return $this->getFields($namePage, 'footer');
    }

    /**
     * Добавить новое поле.
     *
     * @param $namePage
     * @param $section
     * @param ThemeInterface $themeFields
     * @param $type
     * @return \tds\modules\genform\models\data\field\DataField
     */
    public function addField($namePage, $section, ThemeInterface $themeFields, $type) {
        $dataFields = $themeFields->getDefaultValue($type);

        $this->data[$namePage][$section][]= $dataFields;

        $idField = $dataFields->getOptionsId();
        $arrIdFields = $this->getArrIdFields($namePage);

        //Генерация уникального идентификатора
        while ( count(array_intersect($arrIdFields, array($idField))) > 0 ) {
            $idField = strstr($idField, '-',true) . '-' . (substr(strstr($idField, '-',false),1) + 1);
        }
        $dataFields->setOptionsId($idField);
        $dataFields->setName($idField);

        return $dataFields;
    }

    public function deleteField($namePage, $section, $idField) {

        if (isset($this->data[$namePage]) && isset($this->data[$namePage][$section])) {
            $arr = $this->data[$namePage][$section];
            $status = false;
            foreach ($arr as $key => $elem) {
                /** @var DataField $elem */
                if ($elem->getOptionsId() === $idField) {
                    unset($this->data[$namePage][$section][$key]);
                    $this->data[$namePage][$section] = array_values($this->data[$namePage][$section]);
                    $status = true;
                    break;
                }
            }
            return $status;
        } else {
            throw new \Error('Incorrect attribute in function deleteField.');
        }
    }

    /**
     * Получить указаное поле.
     *
     * @param $namePage
     * @param $section
     * @return array
     */
    private function getFields($namePage, $section) {
        if (isset($this->data[$namePage]) && isset($this->data[$namePage][$section])) {
            return $this->data[$namePage][$section];
        }

        return [];
    }

    /**
     * Установить массив данных.
     *
     * @param array $data
     */
    public function setData(array &$data)
    {
        $this->data = &$data;
    }

    /**
     * Получить массив данных.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}