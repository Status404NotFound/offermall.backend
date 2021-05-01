<?php
/**
 * Created by PhpStorm.
 * User: ihor
 * Date: 14.02.17
 * Time: 15:36
 */

namespace tds\modules\genform\models\handlers;

use \common\models\offer\Offer;
use tds\modules\genform\GenForm;
use tds\modules\genform\models\data\DataForm;
use Yii;
use tds\modules\genform\tables\GenFormTable;
use yii\helpers\ArrayHelper;

class OfferHandler
{
    public static function getForms(int $user_id, int $offer_id = null)
    {
        $forms = [];
        $offerModel = new Offer();
        $genFormTableModel = new GenFormTable();

        /**
         * get free forms
         */
        $allUserForms = $genFormTableModel->find()
            ->select(['id', 'name'])
//            ->where(['user_id' => $user_id])
            ->asArray()
            ->all();

        $allUserOfferUsedForms = $offerModel->find()
            ->select(['form_id', 'offer_name'])
            //->where(['owner_id' => $user_id])
            ->andWhere(['not', ['form_id' => null]])
            ->asArray()
            ->all();

        $free_forms = array_diff_key(ArrayHelper::map($allUserForms, 'id', 'name'), ArrayHelper::map($allUserOfferUsedForms, 'form_id', 'offer_name'));


        /**
         * get clone forms
         */
        $allUserOfferUsedForms = $offerModel->find()
            ->select(['id', 'form_id', 'offer_name'])
            //->where(['owner_id' => $user_id])
            ->andWhere(['not', ['form_id' => null]])
            ->andWhere(isset($offer_id) ? ['not', ['id' => $offer_id]] : [])
            ->all();

        $clone_forms_id_title = [];
        foreach ($allUserOfferUsedForms as $offer) {

            $form = $genFormTableModel->find()->where(['id' => $offer->form_id])->one();

            if (!empty($form)) {
                $value = 'Clone(Form: ' . $form->name . ' | Offer:' . $offer->offer_name . ')';
                $clone_forms_id_title[$form->id] = $value;
            }
        }

        /**
         * add current form to forms list
         */
        if (isset($offer_id)) {
            $offer = $offerModel->findOne($offer_id);
            $form = $genFormTableModel->findOne($offer->form_id);
            if (!empty($form)) $forms[$form->id] = $form->name;
        }

        $forms[0] = 'Default form';

        $forms += $clone_forms_id_title;
        $forms += $free_forms;

        return $forms;
    }

    public static function setFormToOffer($form_id, $offer_id)
    {

        $offerById = Offer::findOne(['offer_id' => $offer_id]);
        $x = new GenForm('genform');

        if ($form_id == 0) {
            $modelData = new DataForm(null, $offerById->offer_hash, 'Default form ' . $offer_id);
            $modelData->saveData();
            $offerById->form_id = $modelData->getFormId();
            $offerById->save(false);

            return true;
        }

        $offerByForm = Offer::findOne(['form_id' => $form_id]);

        if (empty($offerByForm) || $offerByForm->offer_id == $offer_id) {
            return true;
        } else {
            $form = GenFormTable::findOne(['id' => $offerByForm->form_id]);

            $modelLoadData = new DataForm($form->id);
            $modelNewData = new DataForm(null, $offerById->offer_hash, 'Clone(' . $form->name . ')');
            $modelNewData->setArrExtension($modelNewData->getArrExtension());
            $modelNewData->setArrHidden($modelLoadData->getArrHidden());
            $modelNewData->setArrPages($modelLoadData->getArrPages());
            $modelNewData->setHash($offerById->offer_hash);
            $modelNewData->saveData();

            $offerById->form_id = $modelNewData->getFormId();
            $offerById->save(false);
            return true;

        }
    }

    public static function editOfferForm($idForm)
    {
        return Yii::$app->response->redirect(['/genform/editor/editor', 'idForm' => $idForm]);
    }

}

