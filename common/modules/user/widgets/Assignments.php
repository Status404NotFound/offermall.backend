<?php


namespace common\modules\user\widgets;

use common\modules\user\components\DbManager;
use common\modules\user\models\Assignment;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;

/**
 * This widget may be used in user update form and provides ability to assign
 * multiple auth items to the user.
 *
 * @author makandy <makandy42@gmail.com>
 */
class Assignments extends Widget
{
    /** @var integer ID of the user to whom auth items will be assigned. */
    public $userId;
    
    /** @var DbManager */
    protected $manager;

    public $model;
    
    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->manager = Yii::$app->authManager;
        if ($this->userId === null && $this->model === null) {
            throw new InvalidConfigException('You should set ' . __CLASS__ . '::$userId');
        }
    }
    
    /** @inheritdoc */
    public function run()
    {
        if ( is_null($this->model) ) {
            $model = Yii::createObject([
                'class'   => Assignment::className(),
                'user_id' => $this->userId,
            ]);
        } else {
            $model = $this->model;
        }
        
        if ($model->load(\Yii::$app->request->post())) {
            $model->updateAssignments();
        }
        
        return $this->render('form', [
            'model' => $model,
        ]);
    }
}