<?php

namespace common\modules\user\models;

use common\modules\user\components\DbManager;
use common\modules\user\helpers\Helper;
use common\modules\user\traits\AuthManagerTrait;
//use common\modules\user\validators\RbacValidator;
use common\modules\user\validators\RbacValidator;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\rbac\Item;
use yii\rbac\Role;

/**
 * Description of Assignment
 *
 * Dependencies:
 * @property-read DbManager $authManager
 *
 * @author makandy <makandy42@gmail.com>
 */
class Assignment extends Model {
    use AuthManagerTrait;

    /**
     * @var integer User id
     */
    public $user_id;

    /**
     * @var \yii\web\IdentityInterface User
     */
    public $user;

    /**
     * @var array
     */
    public $items = [];

    public $role;

    /**
     * @var boolean
     */
    public $updated = false;

    /**
     * @inheritdoc
     */
    public function __construct($id = null, $user = null, $config = array())
    {
        $this->user_id = $id;
        $this->user = $user;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->user_id === null) {
            throw new InvalidConfigException('user_id must be set');
        }

        $this->items = array_keys($this->getAuthManager()->getItemsByUser($this->user_id));
        $this->role = array_keys($this->getAuthManager()->getItemsByUser($this->user_id, Item::TYPE_ROLE));
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'items' => \Yii::t('user', 'Items'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['user_id', 'required'],
            ['items', RbacValidator::className()],
            ['user_id', 'integer']
        ];
    }

    /**
     * Updates auth assignments for user.
     * @return boolean
     */
    public function updateAssignments()
    {
        if (!$this->validate()) {
            return false;
        }

//        echo '<pre>'.print_r($this->items,true).'</pre>';
//        Yii::$app->end();
        if (!is_array($this->items)) {
            $this->items = array($this->items);
        }
        $authManager = $this->getAuthManager();

        $assignedItems = $authManager->getItemsByUser($this->user_id);
        $assignedItemsNames = array_keys($assignedItems);

        foreach (array_diff($assignedItemsNames, $this->items) as $item) {
            $authManager->revoke($assignedItems[$item], $this->user_id);
        }

        foreach (array_diff($this->items, $assignedItemsNames) as $item) {
            $authManager->assign($authManager->getItem($item), $this->user_id);
        }

        $this->updated = true;

        return true;
    }

    /**
     * Returns all available auth items to be attached to user.
     * @return array
     */
    public function getAvailableItems()
    {
        return ArrayHelper::map($this->getAuthManager()->getItems(Item::TYPE_ROLE), 'name', function ($item) {
            return empty($item->description)
                ? $item->name
                : $item->name . ' (' . $item->description . ')';
        });
    }

    /**
     * Grands a roles from a user.
     * @param array $items
     * @return integer number of successful grand
     */
    public function assign(array $items)
    {
        $success = 0;
        $authManager = $this->getAuthManager();
        foreach ($items as $name) {
            try {
                $item = $authManager->getRole($name);
                $item = $item ?: $authManager->getPermission($name);
                /** @var Role $item */
                $authManager->assign($item, $this->user_id);
                $success++;
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
        return $success;
    }

    /**
     * Revokes a roles from a user.
     * @param array $items
     * @return integer number of successful revoke
     */
    public function revoke($items)
    {
        $success = 0;
        $authManager = $this->getAuthManager();
        foreach ($items as $name) {
            try {
                $item = $authManager->getRole($name);
                $item = $item ?: $authManager->getPermission($name);
                /** @var Role $item */
                $authManager->revoke($item, $this->user_id);
                $success++;
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
        return $success;
    }

    /**
     * Get all available and assigned roles/permission
     * @return array
     */
    public function getItems()
    {
        $available = [];
        $authManager = $this->getAuthManager();
        foreach (array_keys($authManager->getRoles()) as $name) {
            $available[$name] = 'role';
        }

        foreach (array_keys($authManager->getPermissions()) as $name) {
            if ($name[0] != '/') {
                $available[$name] = 'permission';
            }
        }

        $assigned = [];
        foreach ($authManager->getAssignments($this->user_id) as $item) {
            $assigned[$item->roleName] = $available[$item->roleName];
            unset($available[$item->roleName]);
        }

        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }


    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if ($this->user) {
            return $this->user->$name;
        }
        return null;
    }
}
