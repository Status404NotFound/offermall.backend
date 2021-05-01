<?php


namespace common\modules\user\commands;

use common\modules\user\models\Route;
use common\modules\user\models\tables\UserChild;
use common\modules\user\UserFinder;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Creates new relation between users account.
 *
 * @property \common\modules\user\Module $module
 *
 * @author makandy <makandy42@gmail.com>
 */
class RouteController extends Controller
{
    /** @var UserFinder */
    protected $UserFinder;

    public function actionIndex() {
        $model = new Route();
        $routes = $model->getRoutes();
        if ( isset($routes['available']) ) {
            $available = $routes['available'];
            $model->addNew($available);
        }

        $this->stdout("Route:\n" . print_r($routes,true) . "\n", Console::FG_GREEN);
    }
}
