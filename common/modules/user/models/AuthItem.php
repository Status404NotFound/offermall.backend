<?php

namespace common\modules\user\models;


use common\modules\user\helpers\Helper;
use common\modules\user\traits\AuthManagerTrait;
use Yii;
use yii\base\Model;
use yii\helpers\Json;
use yii\rbac\Item;
use yii\rbac\Rule;

/**
 * This is the model class for table "tbl_auth_item".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $ruleName
 * @property string $data
 *
 * @property Item $item
 * @property bool isNewRecord
 *
 *
 * Dependencies:
 * @property-read \yii\rbac\ManagerInterface $authManager
 *
 * @author makandy <makandy42@gmail.com>
 * @since 1.0
 */
class AuthItem extends Model {
    use AuthManagerTrait;

    public $name;
    public $type;
    public $description;
    public $ruleName;
    public $data;
    /**
     * @var Item
     */
    private $_item;

    /**
     * Initialize object
     * @param Item  $item
     * @param array $config
     */
    public function __construct($item = null, $config = []) {
        $this->_item = $item;
        if ($item !== null) {
            $this->name = $item->name;
            $this->type = $item->type;
            $this->description = $item->description;
            $this->ruleName = $item->ruleName;
//            $this->data = $item->data === null ? null : Json::encode($item->data);
            $this->data = $item->data;
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['ruleName'], 'checkRule'],
            [['name', 'type'], 'required'],
            [['name'], 'unique', 'when' => function () {
                return $this->isNewRecord || ($this->_item->name != $this->name);
            }],
            [['type'], 'integer'],
            [['description', 'data', 'ruleName'], 'default'],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * Check role is unique
     */
    public function unique() {
        $authManager = $this->authManager;
        $value = $this->name;

        if ($authManager->getRole($value) !== null || $authManager->getPermission($value) !== null) {
            $message = Yii::t('yii', '{attribute} "{value}" has already been taken.');
            $params = [
                'attribute' => $this->getAttributeLabel('name'),
                'value' => $value,
            ];
            $this->addError('name', Yii::$app->getI18n()->format($message, $params, Yii::$app->language));
        }
    }

    /**
     * Check for rule
     */
    public function checkRule() {
        $name = $this->ruleName;
        $authManager = $this->getAuthManager();

        if (!$authManager->getRule($name)) {
            try {
                $rule = Yii::createObject($name);
                if ($rule instanceof Rule) {
                    $rule->name = $name;
                    $authManager->add($rule);
                } else {
                    $this->addError('ruleName', Yii::t('user', 'Invalid rule "{value}"', ['value' => $name]));
                }
            } catch (\Exception $exc) {
                $this->addError('ruleName', Yii::t('user', 'Rule "{value}" does not exists', ['value' => $name]));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'name' => Yii::t('user', 'Name'),
            'type' => Yii::t('user', 'Type'),
            'description' => Yii::t('user', 'Description'),
            'ruleName' => Yii::t('user', 'Rule Name'),
            'data' => Yii::t('user', 'Data'),
        ];
    }

    /**
     * Check if is new record.
     * @return boolean
     */
    public function getIsNewRecord() {
        return $this->_item === null;
    }

    /**
     * Find role
     * @param string $id
     * @return null|\self
     */
    public static function find($id) {
        $item = self::authManager()->getRole($id);
        if ($item !== null) {
            return new self($item);
        }

        return null;
    }

    /**
     * Save role to [[\yii\user\authManager]]
     * @return boolean
     */
    public function save() {
        if ($this->validate()) {
            $authManager = $this->authManager;

            if ($this->_item === null) {
                if ($this->type == Item::TYPE_ROLE) {
                    $this->_item = $authManager->createRole($this->name);
                } else {
                    $this->_item = $authManager->createPermission($this->name);
                }
                $isNew = true;
            } else {
                $isNew = false;
                $oldName = $this->_item->name;
            }
            $this->_item->name = $this->name;
            $this->_item->description = $this->description;
            $this->_item->ruleName = $this->ruleName;
            if ($this->data === null || $this->data === '') {
                $this->_item->data = null;
            } else {
//                    $this->_item->data = Json::decode($this->data);
                    $this->_item->data = $this->data;
            }
            if ($isNew) {
                $authManager->add($this->_item);
            } else {
                /** @var string $oldName */
                $authManager->update($oldName, $this->_item);
            }
            Helper::invalidate();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Adds an item as a child of another item.
     * @param array $items
     * @return int
     */
    public function addChildren(array $items) {
        $success = 0;
        if ($this->_item) {
            $authManager = $this->authManager;

            foreach ($items as $name) {
                $child = $authManager->getPermission($name);
                if ($this->type == Item::TYPE_ROLE && $child === null) {
                    $child = $authManager->getRole($name);
                }
                try {
                    $authManager->addChild($this->_item, $child);
                    $success++;
                } catch (\Exception $exc) {
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }
        if ($success > 0) {
            Helper::invalidate();
        }
        return $success;
    }

    /**
     * Remove an item as a child of another item.
     * @param array $items
     * @return int
     */
    public function removeChildren($items) {
        $success = 0;
        if ($this->_item !== null) {
            $authManager = $this->authManager;

            foreach ($items as $name) {
                $child = $authManager->getPermission($name);
                if ($this->type == Item::TYPE_ROLE && $child === null) {
                    $child = $authManager->getRole($name);
                }
                try {
                    $authManager->removeChild($this->_item, $child);
                    $success++;
                } catch (\Exception $exc) {
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }
        if ($success > 0) {
            Helper::invalidate();
        }
        return $success;
    }

    /**
     * Get items
     * @return array
     */
    public function getItems() {
        $available = [];
        $authManager = $this->authManager;

        if ($this->type == Item::TYPE_ROLE) {
            foreach (array_keys($authManager->getRoles()) as $name) {
                $available[$name] = 'role';
            }
        }
        foreach (array_keys($authManager->getPermissions()) as $name) {
            $available[$name] = $name[0] == '/' ? 'route' : 'permission';
        }

        $assigned = [];
        foreach ($authManager->getChildren($this->_item->name) as $item) {
            $assigned[$item->name] = $item->type == 1 ? 'role' : ($item->name[0] == '/' ? 'route' : 'permission');
            unset($available[$item->name]);
        }
        unset($available[$this->name]);
        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }

    /**
     * Get item
     * @return Item
     */
    public function getItem() {
        return $this->_item;
    }

    /**
     * Get type name
     * @param  mixed $type
     * @return string|array
     */
    public static function getTypeName($type = null) {
        $result = [
            Item::TYPE_PERMISSION => 'Permission',
            Item::TYPE_ROLE => 'Role',
        ];
        if ($type === null) {
            return $result;
        }

        return $result[$type];
    }
}
