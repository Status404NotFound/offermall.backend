<?php


namespace common\modules\user\controllers;

use common\modules\user\UserFinder;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ProfileController shows users profiles.
 *
 * @property \common\modules\user\Module $module
 *
 * @author makandy <makandy42@gmail.com>
 */
class ProfileController extends Controller
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

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    ['allow' => true, 'actions' => ['index','show'], 'roles' => ['@']],
                ],
            ],
        ];
    }

    /**
     * Redirects to current user's profile.
     *
     * @return \yii\web\Response
     */
    public function actionIndex() {
        return $this->redirect(['show', 'id' => \Yii::$app->user->getId()]);
    }

    /**
     * Shows user's profile.
     * Only owner profile has permission.
     *
     * @param int $id
     *
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionShow($id) {
        $profile = $this->UserFinder->findProfileById($id);
        $user = $this->UserFinder->findUserById($id);

        if ($profile === null && $user === null || $id != \Yii::$app->user->id) {
            throw new NotFoundHttpException();
        }

        return $this->render('index', [
            'profile' => $profile,
            'user' => $user,
        ]);
    }
}
