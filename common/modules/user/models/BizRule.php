<?php

namespace common\modules\user\models;

use common\modules\user\traits\AuthManagerTrait;
use Yii;
use yii\base\Model;
use yii\rbac\Item;
use yii\rbac\Rule;

/**
 * BizRule
 *
 * Dependencies:
 * @property-read \yii\rbac\ManagerInterface $authManager
 *
 * @author makandy <makandy42@gmail.com>
 */
class BizRule extends Model {
    use AuthManagerTrait;

    /**
     * @var string name of the rule
     */
    public $name;

    /**
     * @var integer UNIX timestamp representing the rule creation time
     */
    public $createdAt;

    /**
     * @var integer UNIX timestamp representing the rule updating time
     */
    public $updatedAt;

    /**
     * @var string Rule classname.
     */
    public $className;

    /**
     * @var Rule
     */
    private $_item;

    /**
     * Initialize object
     * @param \yii\rbac\Rule $item
     * @param array $config
     */
    public function __construct($item, $config = []) {
        $this->_item = $item;
        if ($item !== null) {
            $this->name = $item->name;
            $this->className = get_class($item);
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name', 'className'], 'required'],
            [['className'], 'string'],
            [['className'], 'classExists']
        ];
    }

    /**
     * Validate class exists
     */
    public function classExists() {
        if (!class_exists($this->className)) {
            $message = Yii::t('user', "Unknown class '{class}'", ['class' => $this->className]);
            $this->addError('className', $message);
            return;
        }
        if (!is_subclass_of($this->className, Rule::className())) {
            $message = Yii::t('user', "'{class}' must extend from 'yii\user\Rule' or its child class", [
                    'class' => $this->className]);
            $this->addError('className', $message);
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'name' => Yii::t('user', 'Name'),
            'className' => Yii::t('user', 'Class Name'),
        ];
    }

    /**
     * Check if new record.
     * @return boolean
     */
    public function getIsNewRecord() {
        return $this->_item === null;
    }

    /**
     * Find model by id
     * @param $id - type
     * @return null|static
     */
    public static function find($id) {
        $item = self::authManager()->getRule($id);
        if ($item !== null) {
            return new static($item);
        }

        return null;
    }

    /**
     * Save model to authManager
     * @return boolean
     */
    public function save() {
        if ($this->validate()) {
            $authManager = $this->authManager;
            $class = $this->className;

            if ($this->_item === null) {
                $this->_item = new $class();
                $isNew = true;
            } else {
                $isNew = false;
                $oldName = $this->_item->name;
            }
            $this->_item->name = $this->name;

            if ($isNew) {
                $authManager->add($this->_item);
            } else {
                /** @var string $oldName */
                $authManager->update($oldName, $this->_item);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Get item
     * @return Item|object
     */
    public function getItem()
    {
        return $this->_item;
    }
}
