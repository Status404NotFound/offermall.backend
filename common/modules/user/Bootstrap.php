<?php


namespace common\modules\user;

use Yii;
use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;
use yii\i18n\PhpMessageSource;
use yii\web\Application;

/**
 * Bootstrap class registers module and user application component. It also creates some url rules which will be applied
 * when UrlManager.enablePrettyUrl is enabled.
 *
 * @author makandy <makandy42@gmail.com>
 */
class Bootstrap implements BootstrapInterface
{
    /** @var array Model's map */
    private $_modelMapTable = [
        'User'             => 'common\modules\user\models\tables\User',
        'Profile'          => 'common\modules\user\models\tables\BaseProfile',
        'Token'            => 'common\modules\user\models\tables\Token',
    ];
    /** @var array Model's map */
    private $_modelMapForm = [
        'RegistrationForm' => 'common\modules\user\models\forms\RegistrationForm',
        'ResendForm'       => 'common\modules\user\models\forms\ResendForm',
        'LoginForm'        => 'common\modules\user\models\forms\LoginForm',
        'SettingsForm'     => 'common\modules\user\models\forms\SettingsForm',
        'RecoveryForm'     => 'common\modules\user\models\forms\RecoveryForm',
        'UserSearch'       => 'common\modules\user\models\search\UserSearch',
    ];
    /** @var array Model's map */
    private $_modelMapSearch = [
        'UserSearch'       => 'common\modules\user\models\search\UserSearch',
    ];

    /**
     * @var array Nav bar items.
     */
    public $navBar;

    /** @inheritdoc */
    public function bootstrap($app)
    {
        /** @var Module $module */
        /** @var \yii\db\ActiveRecord $modelName */
        if ($app->hasModule('user') && ($module = $app->getModule('user')) instanceof Module) {
            //TODO Рефактор 3 последующих цыклов!!!
            /*Table*/
            foreach ($this->_modelMapTable as $name => $definition) {
                $class = "common\\modules\\user\\models\\tables\\" . $name;
                Yii::$container->set($class, $definition);
                $modelName = is_array($definition) ? $definition['class'] : $definition;
                $module->modelMap[$name] = $modelName;
                if (in_array($name, ['User', 'Profile', 'Token'])) {
                    Yii::$container->set($name . 'Query', function () use ($modelName) {
                        return $modelName::find();
                    });
                }
            }

            /*Forms*/
            foreach ($this->_modelMapForm as $name => $definition) {
                $class = "common\\modules\\user\\models\\forms\\" . $name;
                Yii::$container->set($class, $definition);
                $modelName = is_array($definition) ? $definition['class'] : $definition;
                $module->modelMap[$name] = $modelName;
            }

            /*Search*/
            foreach ($this->_modelMapSearch as $name => $definition) {
                $class = "common\\modules\\user\\models\\search\\" . $name;
                Yii::$container->set($class, $definition);
                $modelName = is_array($definition) ? $definition['class'] : $definition;
                $module->modelMap[$name] = $modelName;
            }

            Yii::$container->setSingleton(UserFinder::className(), [
                'userQuery'    => Yii::$container->get('UserQuery'),
                'profileQuery' => Yii::$container->get('ProfileQuery'),
                'tokenQuery'   => Yii::$container->get('TokenQuery'),
            ]);

            if ($app instanceof ConsoleApplication) {
                $module->controllerNamespace = 'common\modules\user\commands';
            } else {
                Yii::$container->set('yii\web\User', [
                    'enableAutoLogin' => true,
                    'loginUrl' => ['/user/security/login'],
                    'identityClass' => $module->modelMap['User'],
                ]);

                $configUrlRule = [
                    'prefix' => $module->urlPrefix,
                    'rules'  => $module->urlRules,
                ];

                if ($module->urlPrefix != 'user') {
                    $configUrlRule['routePrefix'] = 'user';
                }

                $configUrlRule['class'] = 'yii\web\GroupUrlRule';
                $rule = Yii::createObject($configUrlRule);

                $app->urlManager->addRules([$rule], false);
            }

            if (!isset($app->get('i18n')->translations['user*'])) {
                $app->get('i18n')->translations['user*'] = [
                    'class' => PhpMessageSource::className(),
                    'basePath' => __DIR__ . '/messages',
                    'sourceLanguage' => 'en-US'
                ];
            }


            //user did not define the Navbar?
            if ($this->navBar === null && Yii::$app instanceof Application) {
                $this->navBar = [
//                ['label' => Yii::t('user', 'Help'), 'url' => ['default/index']],
                    ['label' => Yii::t('user', 'Application'), 'url' => Yii::$app->homeUrl],
                ];
            }

            Yii::$container->set('common\modules\user\Mailer', $module->mailer);

            $module->debug = $this->ensureCorrectDebugSetting();
        }
    }

    /** Ensure the module is not in DEBUG mode on production environments */
    public function ensureCorrectDebugSetting()
    {
        if (!defined('YII_DEBUG')) {
            return false;
        }
        if (!defined('YII_ENV')) {
            return false;
        }
        if (defined('YII_ENV') && YII_ENV !== 'dev') {
            return false;
        }
        if (defined('YII_DEBUG') && YII_DEBUG !== true) {
            return false;
        }

        return Yii::$app->getModule('user')->debug;
    }
}
