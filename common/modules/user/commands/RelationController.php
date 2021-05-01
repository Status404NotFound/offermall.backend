<?php


namespace common\modules\user\commands;

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
class RelationController extends Controller
{
    /** @var UserFinder */
    protected $UserFinder;

    /**
     * @param string           $id
     * @param \yii\base\Module $module
     * @param UserFinder           $UserFinder
     * @param array            $config
     */
    public function __construct($id, $module, UserFinder $UserFinder, $config = [])
    {
        $this->UserFinder = $UserFinder;
        parent::__construct($id, $module, $config);
    }

    /**
     * This command creates new relation between users account.
     *
     * @param string $usernameParent Login parent
     * @param string $usernameChild Login child
     */
    public function actionIndex($usernameParent, $usernameChild) {
        $parent = $this->UserFinder->findUserByUsername($usernameParent);
        $child = $this->UserFinder->findUserByUsername($usernameChild);

        if ($parent === null) {
            $this->stdout(Yii::t('user', 'User parent is not found') . "\n", Console::FG_RED);
        } elseif ($child === null) {
            $this->stdout(Yii::t('user', 'User child is not found') . "\n", Console::FG_RED);
        } else {
            $parentChild = $child->getModelParent();
            if ( $parentChild === null ||
                $this->confirm(Yii::t('user', 'Are you sure? Current parent of the "' . $parentChild->username . '(' . $parentChild->email. ')"')) ) {
                $relation = new UserChild();
                if ( $relation->setRelation($parent, $child) ) {
                    $this->stdout(Yii::t('user', 'Communication between users it is set') . "\n", Console::FG_GREEN);
                } else {
                    $this->stdout(Yii::t('user', 'Error connection between users it isn\'t established') . "\n", Console::FG_RED);
                }
            }
        }
    }
}
