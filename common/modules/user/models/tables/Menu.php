<?php

namespace common\modules\user\models\tables;

use common\modules\user\traits\AuthManagerTrait;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "menu".
 *
 * @property integer $id Menu id(autoincrement)
 * @property string $name Menu name
 * @property integer $parent Menu parent
 * @property string $route Route for this menu
 * @property integer $order Menu order
 * @property string $data Extra information for this menu
 *
 * @property Menu $menuParent Menu parent
 * @property Menu[] $menus Menu children
 *
 * @author makandy <makandy42@gmail.com>
 * @since 1.0
 */
class Menu extends ActiveRecord {
    use AuthManagerTrait;

    public $parent_name;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%menu}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['parent_name'], 'in',
                'range' => static::find()->select(['name'])->column(),
                'message' => 'Menu "{value}" not found.'],
            [['parent', 'route', 'data', 'order'], 'default'],
            [['parent'], 'filterParent', 'when' => function() {
                return !$this->isNewRecord;
            }],
            [['order'], 'integer'],
            [['route'], 'in',
                'range' => static::getSavedRoutes(),
                'message' => 'Route "{value}" not found.']
        ];
    }

    /**
     * Use to loop detected.
     */
    public function filterParent() {
        $parent = $this->parent;
        $db = static::getDb();
        $query = (new Query)->select(['parent'])
            ->from(static::tableName())
            ->where('[[id]]=:id');
        while ($parent) {
            if ($this->id == $parent) {
                $this->addError('parent_name', 'Loop detected.');
                return;
            }
            $parent = $query->params([':id' => $parent])->scalar($db);
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('user', 'ID'),
            'name' => Yii::t('user', 'Name'),
            'parent' => Yii::t('user', 'Parent'),
            'parent_name' => Yii::t('user', 'Parent Name'),
            'route' => Yii::t('user', 'Route'),
            'order' => Yii::t('user', 'Order'),
            'data' => Yii::t('user', 'Data'),
        ];
    }

    /**
     * Get menu parent
     * @return \yii\db\ActiveQuery
     */
    public function getMenuParent() {
        return $this->hasOne(Menu::className(), ['id' => 'parent']);
    }

    /**
     * Get menu children
     * @return \yii\db\ActiveQuery
     */
    public function getMenus() {
        return $this->hasMany(Menu::className(), ['parent' => 'id']);
    }

    private static $_routes;

    /**
     * Get saved routes.
     * @return array
     */
    public static function getSavedRoutes() {
        if (self::$_routes === null) {
            self::$_routes = [];
            foreach (self::authManager()->getPermissions() as $name => $value) {
                if ($name[0] === '/' && substr($name, -1) != '*') {
                    self::$_routes[] = $name;
                }
            }
        }
        return self::$_routes;
    }

    public static function getMenuSource() {
        $tableName = static::tableName();
        return (new Query())
                ->select(['m.id', 'm.name', 'm.route', 'parent_name' => 'p.name'])
                ->from(['m' => $tableName])
                ->leftJoin(['p' => $tableName], '[[m.parent]]=[[p.id]]')
                ->all(static::getDb());
    }
}
