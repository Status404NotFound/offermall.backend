<?php


namespace common\modules\user\components;

use Yii;
use yii\base\BaseObject;
use yii\caching\Cache;
use yii\helpers\ArrayHelper;
use yii\di\Instance;

/**
 * Configs
 * Used for configure some value. To set config you can use [[\yii\base\Application::$params]]
 *
 * ```
 * return [
 *
 *     'user.cache' => [
 *         'db' => 'customDb',
 *         'menuTable' => '{{%admin_menu}}',
 *         'cache' => [
 *             'class' => 'yii\caching\DbCache',
 *             'db' => ['dsn' => 'sqlite:@runtime/admin-cache.db'],
 *         ],
 *     ]
 * ];
 * ```
 *
 * or use [[\Yii::$container]]
 *
 * ```
 * Yii::$container->set('common\modules\user\components\AdminCache',[
 *     'db' => 'customDb',
 *     'menuTable' => 'admin_menu',
 * ]);
 * ```
 *
 * @author makandy <makandy42@gmail.com>
 * @since 1.0
 */
class UserCache extends BaseObject {

    const CACHE_TAG = 'user.cache';

    /**
     * @var Cache Cache component.
     */
    public $cache = 'cache';

    /**
     * @var integer Cache duration. Default to a hour.
     */
    public $cacheDuration = 3600;

    /**
     * @var self Instance of self
     */
    private static $_instance;

    private static $_classes = [
        'cache' => 'yii\caching\Cache',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        foreach (self::$_classes as $key => $class) {
            try {
                $this->{$key} = empty($this->{$key}) ? null : Instance::ensure($this->{$key}, $class);
            } catch (\Exception $exc) {
                $this->{$key} = null;
                Yii::error($exc->getMessage());
            }
        }
    }

    /**
     * Create instance of self
     */
    public static function instance()
    {
        if (self::$_instance === null) {
            $type = ArrayHelper::getValue(Yii::$app->params, 'user.cache', []);
            if (is_array($type) && !isset($type['class'])) {
                $type['class'] = static::className();
            }

            return self::$_instance = Yii::createObject($type);
        }

        return self::$_instance;
    }

    /**
     * @return Cache
     */
    public static function cache()
    {
        return static::instance()->cache;
    }

    /**
     * @return integer
     */
    public static function cacheDuration()
    {
        return static::instance()->cacheDuration;
    }

}
