<?php
/**
 * Created by PhpStorm.
 * User: andrii
 * Date: 17.01.17
 * Time: 10:53
 */

namespace tds\modules\genform\controllers;


use common\helpers\FishHelper;
use common\models\offer\Offer;
use common\modules\user\models\tables\User;
use tds\modules\genform\models\data\DataForm;
use tds\modules\genform\models\data\field\DataField;
use tds\modules\genform\models\data\FileGenerator;
use tds\modules\genform\models\builder\theme\BootstrapTheme;
use tds\modules\genform\models\builder\theme\Theme;
use tds\modules\genform\models\forms\Calculator;
use tds\modules\genform\models\forms\Edit;
use tds\modules\genform\models\forms\Test;
use tds\modules\genform\models\pages\PageForm;
use tds\modules\genform\tables\GenFormTable;
use yii\base\Exception;
use yii\base\Module;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use Yii;
//TODO: Переписать аргументы id или idForm на что то одно, что бы не было двохсмысленности!



class EditorController extends Controller
{
    /**
     * @return string
     */

    public $user_id;

    public function behaviors()
    {
        return array_merge([
            'cors' => [
                'class' => \yii\filters\Cors::className(),
                #special rules for particular action
                'actions' => [
                    'ajax-get-file-content' => [
                        #web-servers which you alllow cross-domain access
                        'Origin' => ['*'],
                        'Access-Control-Request-Method' => ['POST'],
                        'Access-Control-Request-Headers' => ['*'],
                        'Access-Control-Allow-Credentials' => null,
                        'Access-Control-Max-Age' => 86400,
                        'Access-Control-Expose-Headers' => [],
                    ]
                ],
                #common rules
                'cors' => [
                    'Origin' => [],
                    'Access-Control-Request-Method' => [],
                    'Access-Control-Request-Headers' => [],
                    'Access-Control-Allow-Credentials' => null,
                    'Access-Control-Max-Age' => 0,
                    'Access-Control-Expose-Headers' => [],
                ]
            ],
        ], parent::behaviors());
    }

    private function changeIdentity($user_id)
    {
        $user = User::findOne($user_id);
        $duration = 0;
        Yii::$app->user->switchIdentity($user, $duration);
        Yii::$app->session->set('user.idbeforeswitch', 1);

        return true;
    }

    public function actionGenform(int $user_id, int $form_id = null)
    {
        if (!isset($user_id)) throw new Exception('No User Id!');
        else $this->changeIdentity($user_id);

        if ($form_id != null){
            return $this->redirect(['editor', 'idForm' => $form_id]);
        }else{
            return $this->redirect(['index']);
        }
    }


    public function actionIndex()
    {
        $modelGenFormTable = GenFormTable::find()
//            ->where([
//            'user_id' => \Yii::$app->user->identity->getId(),
//        ])
            ->asArray()->select(['id', 'name'])->all();
        $listForms = ArrayHelper::map($modelGenFormTable,'id','name');
        $listPageForm = [];
        $idForm = null;
        $form = '';

        if (!empty($arrKey = array_keys($listForms))) {

            $idForm = $arrKey[0];

            if (!is_null($id = \Yii::$app->request->post('idForm'))) {
                $idForm = $id;
            }

            $modelTheme = new BootstrapTheme();
            $modelPages = new PageForm();
            $modelData = new DataForm($idForm);
            $modelForm = new Test($modelData, $modelPages, $modelTheme);
            $listPageForm = $modelData->getPages()->getNamePages();
            $form = $modelForm->getHtml() . Html::style($modelForm->getCSS());
        } else {
            //Helper::debug('empty');
        }

        return $this->render('index', [
            'listForms' => $listForms,
            'listPagesForm' => $listPageForm,
            'form' => $form,
            'test' => $idForm,
        ]);
    }

    public function actionCreate()
    {
        if (is_null($paramForm = \Yii::$app->request->post('GenFormTable')) || is_null($paramForm['name'])) {
            throw new \Error('No create The form is not created without valid options "Name"');
        }

        $modelData = new DataForm(null, null, $paramForm['name']);

        $this->redirect(['editor','idForm' => $modelData->getFormId()]);
    }

    public function actionCreateForm($user_id, $offer_id)
    {
        if (Yii::$app->request->isPost){

            if (is_null(Yii::$app->request->post('form-name')) || is_null(Yii::$app->request->post('offer_id'))) {
                throw new \Error('No create The form is not created without valid options "Name"');
            }else{
                $modelData = new DataForm(null, null, Yii::$app->request->post('form-name'), Yii::$app->request->post('offer_id'));
                $this->redirect(['editor','idForm' => $modelData->getFormId()]);
            }

        }else{

            if (!isset($user_id)) throw new Exception('No User Id!');
            else $this->changeIdentity($user_id);

            if (!isset($offer_id)) throw new Exception('No Offer Id!');

            $offer = Offer::find()->where(['offer_id' => $offer_id])->one();
            if (is_null($offer)) throw new Exception('Offer Id is Null!');

            return $this->render('create-form', [
                'offer_name' => $offer->offer_name,
                'offer_id' => $offer_id,
            ]);
        }

    }

    public function actionClone()
    {
        $paramForm = \Yii::$app->request->post('GenFormTable');
        if (is_null($paramForm) || is_null($paramForm['name']) || is_null($paramForm['id'])) {
            throw new \Error('No create The form is not created without valid options "Name"');
        }

        $modelLoadData = new DataForm($paramForm['id']);
        $modelNewData = new DataForm(null, null, $paramForm['name'], null);

        $modelNewData->setArrExtension($modelNewData->getArrExtension());
        $modelNewData->setArrHidden($modelLoadData->getArrHidden());
        $modelNewData->setArrPages($modelLoadData->getArrPages());
        $modelNewData->setNameForm($paramForm['name']);

        $modelNewData->saveData();

        $this->redirect(['index']);
    }

    /**
     *
     * @param $idForm
     * @param $nameForm
     * @return string
     * @throws \Error
     */

    public function actionEditor($idForm, $nameForm = null)
    {
        $post = \Yii::$app->request->post();
        $namePage = 'form';
        $modelData = new DataForm($idForm, null, $nameForm);
        $modelTheme = new BootstrapTheme();
        $modelPages = new PageForm();

        if (isset($post['event'])) {
            if (isset($post['data']) && isset($post['element'])) {
                $data = Json::decode($post['data']);
                $element = Json::decode($post['element']);
                $keyPosition = implode(array_map(function ($elem) use ($element){
                    static $x = -1;$x++;

                    return ($elem['id'] == $element['id'])?$x:'';
                },$data));

                switch ($post['event']) {
                    case 'create':
                        if ( isset($element['type']) ) {
                            $modelData->getPages()->addField($namePage, 'content', $modelTheme, $element['type']);
                            $modelData->getPages()->moveField($namePage, 'content', count($data)-1, $keyPosition);
                            $modelData->saveData();
                        }
                        break;
                    case 'delete':
                        if (isset($element['id'])) {
                            $modelData->getPages()->deleteField($namePage,'content', $element['id']);
                            $modelData->saveData();
                        }
                        break;
                    case 'update':
                        if (isset($element['id'])) {
                            $arrIdFields = $modelData->getPages()->getArrIdFields($namePage);
                            $modelData->getPages()->moveField($namePage, 'content', array_search($element['id'], $arrIdFields), $keyPosition);
                            $modelData->saveData();
                        }
                        break;
                    default :
                    throw new \Error('Unknown event!');
                }
            } elseif ( isset($post['data']) ) {
                $data = $post['data'];
                if ( isset($data['id']) ) {
                    $idField = $data['id'];
                    if ( !is_null($dataField = $modelData->getPages()->getField($namePage, 'header', $idField)) ) {
                        $dataField->parseRequestData($data);
                        $modelData->saveData();
                    }
                    if ( !is_null($dataField = $modelData->getPages()->getField($namePage, 'content', $idField)) ) {
                        $dataField->parseRequestData($data);
                        $modelData->saveData();
                    }
                    if ( !is_null($dataField = $modelData->getPages()->getField($namePage, 'footer', $idField)) ) {
                        $dataField->parseRequestData($data);
                        $modelData->saveData();
                    }
                }
            } else {
                throw new \Error('Missing data!');
            }
        }

        $this->view->params['nameForm'] = $modelData->getNameForm();

        $modelForm = new Edit($modelData, $modelPages, $modelTheme);
        $form = $modelForm->getHtml();
        $form .= Html::style($modelForm->getCSS());

        return $this->render('editor', [
            'modelForm' => $form,
            'modelTheme' => $modelTheme,
            'idForm' => $idForm
        ]);
    }


    public function actionAjaxGetFileContent($form_id){
        if (\Yii::$app->request->isGet) {

            $file = \Yii::$app->basePath . '/web/offer_forms/' . $form_id . '/form.html';
            $content = file_get_contents($file);

            \Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
            return json_encode($content);
        }else{
            return 'Error';
        }
    }


    /**
     * Страница редактора "страницы калькулятора".
     *
     * @param $idForm
     * @param null $nameForm
     * @return string
     */


    public function actionCalculator($idForm, $nameForm = null)
    {

        $modelTheme = new BootstrapTheme();
        $modelPages = new PageForm();
        $modelData = new DataForm($idForm, null, $nameForm);
        $modelForm = new Edit($modelData, $modelPages, $modelTheme);

        $this->view->params['nameForm'] = $modelData->getNameForm();

        $form = $modelForm->getHtml();
        $form .= Html::style($modelForm->getCSS());

        return $this->render('calculator', [
            'modelForm' => $form,
            'modelTheme' => $modelTheme,
        ]);
    }

    /**
     * Страница редактор стилей формы
     *
     * @param $idForm
     * @param null $nameForm
     * @return string
     */
    public function actionDesign($idForm, $nameForm =null)
    {
        $modelTheme = new BootstrapTheme();
        $modelPages = new PageForm();
        $modelData = new DataForm($idForm, null, $nameForm);
        $modelForm = new Edit($modelData, $modelPages, $modelTheme);

        $this->view->params['nameForm'] = $modelData->getNameForm();

        $form = $modelForm->getHtml();
        $form .= Html::style($modelForm->getCSS());

        return $this->render('design', [
            'modelForm' => $form,
            'modelTheme' => $modelTheme,
        ]);
    }

    /**
     * Страница настройки/подключения модулей
     *
     * @return string
     */
    public function actionModule()
    {
        return $this->render('module');
    }

    /**
     * Страница опцый формы
     *
     * @return string
     */
    public function actionOptions()
    {
        return $this->render('options');
    }


    public function actionSource($idForm)
    {
        $gen = new FileGenerator($idForm);
        $f = $gen->genMainJsScript();
        return $this->render('source', [
            'javaScriptCode' => $f,
        ]);
    }

    public function actionTest($idForm)
    {
        $modelForm = new Test(
            new DataForm($idForm),
            new PageForm(),
            new BootstrapTheme()
        );

        $form = $modelForm->getHtml() . Html::style($modelForm->getCSS());

        echo $form;
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the GenFormTable model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * RED WEB TEST FORM GET ZET 123 456 789
     * @return GenFormTable the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = GenFormTable::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
