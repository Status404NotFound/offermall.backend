<?php

namespace common\models;


use common\models\core\Countries;
use linslin\yii2\curl\Curl;
use Yii;
use yii\base\Model;
use \app\models\core\GeneratorFormTable;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class Plain extends Model
{
    private static function setFields($arrFields, $param)
    {
        $nameField = $param['name'];

        if (isset($param['required']) && $param['required'] == true) {
            $param['required'] = 1;
        } else {
            $param['required'] = 0;
        }

//        Helper::debug($nameField);
        if (isset($arrFields[$nameField])) {
            foreach ($arrFields[$nameField] as $key => $value) {
                if (!isset($param[$key])) continue;
                if (strstr($key, 'items')) {
                    $arrFields[$nameField][$key] = Json::decode($param[$key]);
                } else {
                    $arrFields[$nameField][$key] = $param[$key];
                }
            }

        }

//        Helper::debug([$arrFields,$param]);


        return $arrFields;
    }

    public static function saveFields(Offer $modelOffer, $param)
    {
        //Ідентифікатор офера
        $offer_id = $param['id'];
        //Отримання стандартних полів
        $arrFieldsOfferDefault = GeneratorForm::getFieldsDefault($modelOffer->fields);

        if (!$model = GeneratorFormTable::findOne(['offer_id' => $offer_id])) {
            //Якщо форма не була створена попередньо:
            //Створити її і призначити офер
            $model = new GeneratorFormTable();
            $model->offer_id = $offer_id;
            //Зберегти дані в таблицю
            $model->title = 'Title';
            $model->buttons_options = Json::encode([
                'btn_calc' => 'Calculate',
                'btn_back' => 'Back',
                'btn_offer' => 'Submit',
                'btn_thank_pages' => 'Ok'
            ]);
            $model->pattern_thank_page = Json::encode([
                'pattern' => '',
                'header' => 'Thank you for your order',
            ]);
            $model->options_fields_offer = Json::encode($arrFieldsOfferDefault);
            $model->options_fields_calc = Json::encode([]);
            $model->fields_calc = Json::encode([]);
            $model->formula_calc = Json::encode([
                'pattern' => 'Price: {price}$',
                'formula' => [
                    'price' => '2+2'
                ]
            ]);
            $model->color_them = Json::encode([]);
            $model->enable_calc = 0;
            $model->enable_pay = 0;
            $model->param_pay = Json::encode([
                'liqpay' => [
                    'amount' => '0',
                    'currency' => 'USD',
                    'description' => '',
                    'order_id' => '',
                    'public_key' => '',
                    'private_key' => '',
                ],
                'paypal' => [
                    'amount' => '0',
                    'currency' => 'USD',
                    'clientId' => '',
                    'clientSecret' => '',
                ]
            ]);

        }
        $arrFieldsCalcDefault = GeneratorForm::getFieldsDefault($model->fields_calc);


        if ($param['form'] === 'offer') {
            //Якщо форма належить оферу:
            $fieldsData = Json::decode($model->options_fields_offer);

            foreach ($arrFieldsOfferDefault as $key => $value) {
                if (isset($fieldsData[$key])) {
                    $arrFieldsOfferDefault[$key] = $fieldsData[$key];
                }
            }
//            Helper::debug($arrFieldsOfferDefault);
            //Записати нові значення якщо були зміни в полях


            if (isset($param['type'])) {
                //Це поле
                $arrFieldsOfferDefault = self::setFields($arrFieldsOfferDefault, $param);
            } else if ($param['name'] === 'title') {
                //Це заголовок
                $model->title = $param['text'];
            } else {
                //Це кнопка
                $buttons = Json::decode($model->buttons_options);
                $buttons[$param['name']] = $param['text'];
                $model->buttons_options = Json::encode($buttons);
            }
            //Зберегти дані в таблицю
            $model->options_fields_offer = Json::encode($arrFieldsOfferDefault);


        } else if ($param['form'] === 'calculator') {
            //Якщо форма належить калькулятору:
            $fieldsData = Json::decode($model->options_fields_calc);

            foreach ($arrFieldsCalcDefault as $key => $value) {
                if (isset($fieldsData[$key])) {
                    $arrFieldsCalcDefault[$key] = $fieldsData[$key];
                }
            }
            //Записати нові значення якщо були зміни в полях
            if (isset($param['type'])) {
                //Це поле
                $arrFieldsCalcDefault = self::setFields($arrFieldsCalcDefault, $param);
            } else if ($param['name'] === 'title') {
                //Це заголовок
                $model->title = $param['text'];
            } else {
                //Це кнопка
                $buttons = Json::decode($model->buttons_options);
                $buttons[$param['name']] = $param['text'];
                $model->buttons_options = Json::encode($buttons);
            }
            //Зберегти дані в таблицю
            $model->options_fields_calc = Json::encode($arrFieldsCalcDefault);
        } else if ($param['form'] === 'thank-pages') {
            //Записати нові значення якщо були зміни в полях
            if ($param['name'] === 'pattern') {
                //Це шаблон
                $pattern = Json::decode($model->pattern_thank_page);
                $pattern[$param['name']] = $param['pattern'];
                $model->pattern_thank_page = Json::encode($pattern);
            } else if ($param['name'] === 'header') {
                //Це заголовок
                $pattern = Json::decode($model->pattern_thank_page);
                $pattern[$param['name']] = $param['text'];
                $model->pattern_thank_page = Json::encode($pattern);
            } else {
                //Це кнопка
                $buttons = Json::decode($model->buttons_options);
                $buttons[$param['name']] = $param['text'];
                $model->buttons_options = Json::encode($buttons);
            }
        }

//        Helper::debug($arrFieldsOfferDefault);
        $model->save();
        //Віддати масив полів для формування форми
        return [
            'fields_offer' => $arrFieldsOfferDefault,
            'fields_calc' => $arrFieldsCalcDefault,
            'title' => Html::decode($model->title),
            'calculator' => Json::decode($model->formula_calc),
            'buttons' => Json::decode($model->buttons_options),
            'pattern_thank_page' => Json::decode($model->pattern_thank_page),
            'them' => Json::decode($model->color_them),
            'enable_calc' => $model->enable_calc,
        ];
    }

    public static function sortCalculator($fields, $offer_id, $nameField, $typeField)
    {
        //Отримання моделі форми
        if ($model = GeneratorFormTable::findOne(['offer_id' => $offer_id])) {
            //Зберегти новий порядок полів
            $model->fields_calc = $fields;

            //Отримання стандартних полів
            $arrFieldsCalcDefault = GeneratorForm::getFieldsDefault($model->fields_calc);

            //Отримати список опцій полів:
            $fieldsData = Json::decode($model->options_fields_calc);

            //Перезапишем стандартні дані - даними з моделі форми
            foreach ($arrFieldsCalcDefault as $key => $value) {
                if (isset($fieldsData[$key])) {
                    $arrFieldsCalcDefault[$key] = $fieldsData[$key];
                }
            }
            //Записати нові значення якщо були зміни в полях
            if ($nameField !== '' && $typeField !== '') {
                $arrFieldsCalcDefault = self::setFields($arrFieldsCalcDefault, ['name' => $nameField, 'type' => $typeField]);
            }
            //Зберегти дані в таблицю
            $model->options_fields_calc = Json::encode($arrFieldsCalcDefault);

            return $model->update();
        }

        return false;
    }

    public static function sortOffer($fields, $offer_id, $nameField, $typeField)
    {
        //О тримання моделі форми
        if ($model = GeneratorFormTable::findOne(['offer_id' => $offer_id])) {

            $modelOffer = self::findModelOffer($offer_id);
            $modelOffer->fields = $fields;
            $modelOffer->update();


            //Отримання стандартних полів
            $arrFieldsOfferDefault = GeneratorForm::getFieldsDefault($modelOffer->fields);

            //Отримати список опцій полів:
            $fieldsData = Json::decode($model->options_fields_offer);

            //Перезапишем стандартні дані - даними з моделі форми
            foreach ($arrFieldsOfferDefault as $key => $value) {
                if (isset($fieldsData[$key])) {
                    $arrFieldsOfferDefault[$key] = $fieldsData[$key];
                }
            }

            //Записати нові значення якщо були зміни в полях
            $arrFieldsOfferDefault = self::setFields($arrFieldsOfferDefault, ['name' => $nameField, 'type' => $typeField]);

            //Зберегти дані в таблицю
            $model->options_fields_offer = Json::encode($arrFieldsOfferDefault);

            return $model->update();
        }


        return false;
    }

    public static function loadFields(Offer $modelOffer)
    {
        //Отримання стандартних полів для форми офера
        $arrFieldsOfferDefault = self::getFieldsDefault($modelOffer->fields);

        if (!$model = GeneratorFormTable::findOne(['offer_id' => $modelOffer->id])) {
            //Якщо форма не була створена попередньо:
            //Створити її і призначити офер
            $model = new GeneratorFormTable();
            $model->offer_id = $modelOffer->id;

            //Зберегти дані в таблицю
            $model->title = 'Title';
            $model->buttons_options = Json::encode([
                'btn_calc' => 'Calculate',
                'btn_back' => 'Back',
                'btn_offer' => 'Submit',
                'btn_thank_pages' => 'Ok'
            ]);
            $model->pattern_thank_page = Json::encode([
                'pattern' => '',
                'header' => 'Thank you for your order',
            ]);
            $model->options_fields_offer = Json::encode($arrFieldsOfferDefault);
            $model->options_fields_calc = Json::encode([]);
            $model->fields_calc = Json::encode([]);
            $model->formula_calc = Json::encode([
                'pattern' => 'Price: {price}$',
                'formula' => [
                    'price' => '2+2'
                ]
            ]);
            $model->color_them = Json::encode([]);
            $model->enable_calc = 0;
            $model->enable_pay = 0;
            $model->param_pay = Json::encode([
                'liqpay' => [
                    'amount' => '0',
                    'currency' => 'USD',
                    'description' => '',
                    'order_id' => '',
                    'public_key' => '',
                    'private_key' => '',
                ],
                'paypal' => [
                    'amount' => '0',
                    'currency' => 'USD',
                    'clientId' => '',
                    'clientSecret' => '',
                ]
            ]);

            $model->save();
        }
        //Якщо форма вже існує:
        //Отримати кольорову тему форми
        $arrTheme = self::themeDefault($model->color_them);
        //Отримання стандартних полів для форми офера
        $arrFieldsCalcDefault = self::getFieldsDefault($model->fields_calc);
        //Отримати значення полів форми офера и калькулятора
        $fieldsOfferData = Json::decode($model->options_fields_offer);
        $fieldsCalcData = Json::decode($model->options_fields_calc);
        //Внести зміни до стандартних полів
        foreach ($arrFieldsOfferDefault as $key => $value) {
            if (isset($fieldsOfferData[$key])) {
                $arrFieldsOfferDefault[$key] = $fieldsOfferData[$key];
            }
        }
        foreach ($arrFieldsCalcDefault as $key => $value) {
            if (isset($fieldsCalcData[$key])) {
                $arrFieldsCalcDefault[$key] = $fieldsCalcData[$key];
            }
        }

        //Віддати масив полів для формування форми
        return [
            'fields_offer' => $arrFieldsOfferDefault,
            'fields_calc' => $arrFieldsCalcDefault,
            'title' => Html::decode($model->title),
            'calculator' => Json::decode($model->formula_calc),
            'buttons' => Json::decode($model->buttons_options),
            'pattern_thank_page' => Json::decode($model->pattern_thank_page),
            'them' => $arrTheme,
            'enable_calc' => $model->enable_calc,
        ];
    }

    private function themeDefault($themeCustom = null)
    {
        $theme = [
            'formHeader' => [
                'background' => '#2c3e50'
            ],
            'formWrapper' => [
                'background' => '#ffffff'
            ],
            'btnBack' => [
                'background' => '#dddddd'
            ],
            'btnCalculator' => [
                'background' => '#dddddd'
            ],
            'btnSend' => [
                'background' => '#dddddd'
            ],
        ];

        if (isset($themeCustom) && ($arrCustom = Json::decode($themeCustom)) && is_array($arrCustom)) {
            //Обход полей
            foreach ($theme as $key => $value) {
                if (!isset($arrCustom[$key])) continue;
                $theme[$key] = $arrCustom[$key];
            }
        }

        return $theme;
    }

    public static function setTheme($modelForm, $paramTheme)
    {
        $themeForm = GeneratorForm::themeDefault($modelForm->color_them);

        if (isset($paramTheme) && is_array($paramTheme)) {
            foreach ($paramTheme as $key => $value) {
                if (!isset($themeForm[$key])) continue;
                $themeForm[$key] = $paramTheme[$key];
            }
        }

//        Helper::debug($themeForm);
        return Json::encode($themeForm);
    }

    public static function renderForm($param, $modelOffer, $useCalculator = true, $product = false)
    {
        //Валідація даних
        if (!(isset($param) && is_array($param))) {
            return null;
        }

        //Формування шаблону
        if ($param['pattern_thank_page']['pattern'] === '') {
            $pattern = '';
            foreach ($param['fields_offer'] as $value) {
                $pattern .= '<p>' . $value["label"] . ': {' . strtolower($value["label"]) . '}</p>';
            }
            $pattern .= '<hr>';
            $pattern .= '<h3 class="text-center">Price: {price}</h3>';
            $param['pattern_thank_page']['pattern'] = $pattern;
        } else {
            $pattern = $param['pattern_thank_page']['pattern'];
        }
        if ($product) {
            $form = '<form class="orderformcdn" action="http://' . Yii::$app->params['domainName'] . '/form/' . $modelOffer->hash . '" method="GET">' . PHP_EOL;
        } else {
            $form = '<form class="orderformcdn" action="http://production.dev/generator-form/test" method="POST">' . PHP_EOL;
        }

        $form .= '    <input type="hidden" name="ip" value="ip_form">' . PHP_EOL .
            '    <input type="hidden" name="cookie" class="adfsh-ck">' . PHP_EOL .
            '    <input type="hidden" name="view_hash" class="orderViewHash">' . PHP_EOL .
            '    <input type="hidden" name="sid" value="sid_form">' . PHP_EOL;

        //Заголовок
        $form .= '<div class="form-header">' . PHP_EOL;
        $form .= '    <h3>' . $param['title'] . '</h3>' . PHP_EOL;
        $form .= '</div>' . PHP_EOL;


        //Контент
        $form .= '<div class="form-content">' . PHP_EOL;

        if ($useCalculator):
            $form .= '<div class="wrapper-calculator">' . PHP_EOL;
            $form .= '   <br>' . PHP_EOL;
            foreach ($param['fields_calc'] as $key => $value):
                $form .= self::renderField($key, $value, $modelOffer->id, 'calculator', false, false);
            endforeach;

            $form .= '</div>' . PHP_EOL;

            $form .= '<div class="wrapper-offer" style="display: none;">' . PHP_EOL;
            $form .= '    <br>' . PHP_EOL;
            foreach ($param['fields_offer'] as $key => $value):
                $form .= self::renderField($key, $value, $modelOffer->id, 'offer', false, false);
            endforeach;

            $form .= '<div class="form-group hidden preview-calculate">';
            $form .= '    <div class="col-lg-12 fields">';
            $form .= '        <pre class="result-calculate">' . $param['calculator']['pattern'] . '</pre>';
            $form .= '    </div>';
            $form .= '    <div class="col-lg-12"><p class="help-block"></p></div>';
            $form .= '    <div class="clearfix"></div>';
            $form .= '</div>';

            $form .= '</div>' . PHP_EOL;
        else:

            $form .= '<div class="wrapper-offer" style="display: block;">' . PHP_EOL;
            $form .= '    <br>' . PHP_EOL;
            foreach ($param['fields_offer'] as $key => $value):
                $form .= self::renderField($key, $value, $modelOffer->id, 'offer', false, false);
            endforeach;
            $form .= '</div>' . PHP_EOL;
        endif;
        $form .= '<div class="wrapper-thank-page" style="display: none;">' . PHP_EOL;

        $form .= '    <h3 class="text-center">' . $param['pattern_thank_page']['header'] . '</h3>' . PHP_EOL;

        $form .= '    <div class="form-group">' . PHP_EOL;
        $form .= '        <div class="col-lg-12 fields">' . PHP_EOL;
        $form .= '            <pre class="result-calculator">' . PHP_EOL;

        $form .= $pattern;

        $form .= '            </pre>' . PHP_EOL;
        $form .= '        </div>' . PHP_EOL;

        $form .= '        <div class="col-lg-12">' . PHP_EOL;
        $form .= '            <p class="help-block"></p>' . PHP_EOL;
        $form .= '        </div>' . PHP_EOL;

        $form .= '        <div class="clearfix"></div>' . PHP_EOL;
        $form .= '    </div>' . PHP_EOL;
        $form .= '</div>' . PHP_EOL;


        $form .= '</div>' . PHP_EOL;

        //Подвал
        $form .= '<div class="form-footer">' . PHP_EOL;

        if ($useCalculator):
            $form .= '    <div class="wrapper-calculator">' . PHP_EOL;
            $form .= '        <div class="form-group">' . PHP_EOL;
            $form .= '            <button type="button" class="btn btn-calculator btn-lg">' . $param['buttons']['btn_calc'] . '</button>' . PHP_EOL;
            $form .= '        <div class="clearfix"></div>' . PHP_EOL;
            $form .= '        </div>' . PHP_EOL;
            $form .= '    </div>' . PHP_EOL;

            $form .= '    <div class="wrapper-offer"  style="display: none">' . PHP_EOL;
            $form .= '        <div class="form-group">' . PHP_EOL;
            $form .= '            <button type="button" class="btn btn-back btn-lg pull-left" style=" width: 55px; margin: 0 0 0 15px;">←</button>' . PHP_EOL;
            $form .= '            <button type="button" class="btn btn-send btn-lg">' . $param['buttons']['btn_offer'] . '</button>' . PHP_EOL;
            $form .= '        </div>' . PHP_EOL;
            $form .= '    </div>' . PHP_EOL;
        else:
            $form .= '    <div class="wrapper-offer">' . PHP_EOL;
            $form .= '        <div class="form-group">' . PHP_EOL;
            $form .= '            <button type="button" class="btn btn-send btn-lg">' . $param['buttons']['btn_offer'] . '</button>' . PHP_EOL;
            $form .= '        </div>' . PHP_EOL;
            $form .= '    </div>' . PHP_EOL;
        endif;

        $form .= '    <div class="wrapper-thank-page" style="display: none">' . PHP_EOL;
        $form .= self::renderPay($modelOffer);
        $form .= '        <div class="form-group">' . PHP_EOL;
        $form .= '            <button type="button" class="btn btn-thank-pages btn-lg">' . $param['buttons']['btn_thank_pages'] . '</button>' . PHP_EOL;
        $form .= '        </div>' . PHP_EOL;
        $form .= '    </div>' . PHP_EOL;

        $form .= '</div>' . PHP_EOL;

        $form .= Html::endTag('form');

        return $form;
    }

    public static function renderPay($modelOffer) {
        $modelForm = GeneratorFormTable::find()->where(['offer_id' => $modelOffer->id])->select(['param_pay'])->one();
        $paramPay = Json::decode($modelForm->param_pay);
        $form = '        <div class="form-group text-center">' . PHP_EOL;
        if ( isset($paramPay['liqpay']) && isset($paramPay['liqpay']['private_key']) && isset($paramPay['liqpay']['public_key']) ) {
            $form .= Html::a(
                    '<img src="https://static.liqpay.com/logo/liqpay5.png" alt="Pay with LiqPay">',
                    Url::to('http://' . Yii::$app->params['domainName'] . '/' . $modelOffer->hash . '/sid_form/liqpay.html')
                ) . PHP_EOL;
        }
        $form .= '</div>' . PHP_EOL;
        return $form;
    }

    public static function renderFormCalc($param, $id_offer, $popoverStatus = true, $blockStatus = true, $formFormula = false)
    {
        //Валідація даних
        if (!(isset($param) && isset($id_offer) && is_array($param))) {
            return null;
        }

        $fields = $param['fields_calc'];

        $form = Html::beginTag('div', ['class' => 'generatorForm',]);

        //Заголовок
        $form .= '<div class="form-header popover-elem" ' . self::popoverFormField(
                'title',
                ['type' => 'title', 'text' => $param['title']],
                $id_offer,
                'offer', $popoverStatus) . '>';
        $form .= '<h3>' . $param['title'] . '</h3>';
        $form .= '</div>';
        $form .= '<br>';

        //Контент
        $form .= '<div class="form-content">';
        $form .= '<div class="wrapper-calculator">';
        foreach ($fields as $key => $value):
            if ($formFormula) {
                $form .= self::renderField($key, $value, $id_offer, 'calculator', $popoverStatus, $blockStatus, true);
            } else {
                $form .= self::renderField($key, $value, $id_offer, 'calculator', $popoverStatus, $blockStatus);
            }
        endforeach;
        $form .= '<div class="form-group">
                    <div class="col-lg-12 fields">
                        <pre class="result-calculate">' . $param['calculator']['pattern'] . '</pre>
                    </div>
                    <div class="col-lg-12"><p class="help-block"></p></div>
                    <div class="clearfix"></div>
                </div>';
        $form .= '</div>';
        $form .= '</div>';

        //Подвал
        $form .= '<div class="form-footer">';
        $form .= '<div class="wrapper-calculator">';
        $form .= '<div class="form-group">';
        $form .= '<button type="button" class="btn btn-calculator btn-lg calc"' .
            self::popoverFormField(
                'btn_calc',
                ['type' => 'button', 'text' => $param['buttons']['btn_calc']],
                $id_offer,
                'calculator',
                $popoverStatus) . '>' . $param['buttons']['btn_calc'] . '</button>';
        $form .= '</div>';
        $form .= '</div>';
        $form .= '</div>';

        $form .= Html::endTag('div');

        return $form;
    }

    public static function renderFormThankPages($param, $id_offer, $popoverStatus = true)
    {
        //Валідація даних
        if (!(isset($param) && isset($id_offer) && is_array($param))) {
            return null;
        }

        //Формування шаблону
        if ($param['pattern_thank_page']['pattern'] === '') {
            $pattern = '';
            foreach ($param['fields_offer'] as $value) {
                $pattern .= '<p>' . $value["label"] . ':{' . strtolower($value["label"]) . '}</p>';
            }
            $pattern .= '<hr>';
            $pattern .= '<h3 class="text-center">Price: {price}</h3>';
            $param['pattern_thank_page']['pattern'] = $pattern;
        } else {
            $pattern = $param['pattern_thank_page']['pattern'];
        }


        //Початок форми
        $form = Html::beginTag('div', ['class' => 'generatorForm',]);

        //Заголовок
        $form .= '<div class="form-header popover-elem" ' . self::popoverFormField(
                'title',
                ['type' => 'title', 'text' => $param['title']],
                $id_offer,
                'offer', $popoverStatus) . '>';
        $form .= '<h3>' . $param['title'] . '</h3>';
        $form .= '</div>';

        $form .= '<h3 class="text-center"' . self::popoverFormField(
                'header',
                ['type' => 'title', 'text' => $param['pattern_thank_page']['header']],
                $id_offer,
                'thank-pages') . '>' . $param['pattern_thank_page']['header'] . '</h3>';

        //Контент
        $form .= '<div class="form-content">';
        $form .= '<div class="form-group">
                    <div class="col-lg-12 fields">
                        <pre class="result-calculator"' . self::popoverFormField(
                'pattern',
                ['type' => 'pattern-thank-page', 'text' => $pattern],
                $id_offer,
                'thank-pages') . '>';

        $form .= $pattern;

        $form .= '      </pre>
                    </div>
                    <div class="col-lg-12"><p class="help-block"></p></div>
                    <div class="clearfix"></div>
                </div>';
        $form .= '</div>';

        //Подвал
        $form .= '<div class="form-footer">';
        $form .= '<div class="form-group">';


        $form .= '<div class="frontend-btn"' . self::popoverFormField(
                'btn_thank_pages',
                ['type' => 'button', 'text' => $param['buttons']['btn_thank_pages']],
                $id_offer,
                'thank-pages') . '></div>';
        $form .= '<button type="button" class="btn btn-thank-pages btn-lg">' . $param['buttons']['btn_thank_pages'] . '</button>';
        $form .= '</div>';
        $form .= '</div>';

        $form .= Html::endTag('div');

        return $form;
    }

    public static function renderFormOffer($param, $id_offer, $popoverStatus = true)
    {
        //Валідація даних
        if (!(isset($param) && isset($id_offer) && is_array($param))) {
            return null;
        }

        $fields = $param['fields_offer'];

        $form = Html::beginTag('div', ['class' => 'generatorForm']);

        //Заголовок
        $form .= '<div class="form-header popover-elem" ' . self::popoverFormField(
                'title',
                ['type' => 'title', 'text' => $param['title']],
                $id_offer,
                'offer', $popoverStatus) . '>';
        $form .= '<h3>' . $param['title'] . '</h3>';
        $form .= '</div>';
        $form .= '<br>';

        //Контент
        $form .= '<div class="form-content">';
        $form .= '<div class="wrapper-offer">';
        foreach ($fields as $key => $value):
            $form .= self::renderField($key, $value, $id_offer, 'offer', $popoverStatus);
        endforeach;
        $form .= '</div>';
        $form .= '</div>';

        //Подвал
        $form .= '<div class="form-footer">';
        $form .= '<div class="form-group">';
        $form .= '<div class="frontend-btn"' . self::popoverFormField(
                'btn_offer',
                ['type' => 'button', 'text' => $param['buttons']['btn_offer']],
                $id_offer,
                'offer',
                $popoverStatus) . '></div>';
        $form .= '<button type="button" class="btn btn-send btn-lg">' . $param['buttons']['btn_offer'] . '</button>';
        $form .= '</div>';
        $form .= '</div>';

        $form .= Html::endTag('div');

        return $form;
    }

    public static function renderField($nameField, $param, $id_offer, $typeForm, $popoverStatus = true, $blockStatus = true, $blockContent = false)
    {

//        Helper::debug([$nameField,$param,$popoverStatus]);

        if ($typeForm == '')  {
            $param['required'] = 1;
        }


        $form = '<div class="form-group" ' . self::popoverFormField($nameField, $param, $id_offer, $typeForm, $popoverStatus) . '>';
        if ($popoverStatus || $blockStatus):

            if ($blockContent) :
                switch ($param['type']) :
                    case 'text':
                        $blockContent = Html::button($nameField, ['class' => 'btn btn-success btn-sm']);
                        break;
                    case 'integer':
                        $blockContent = Html::button($nameField, ['class' => 'btn btn-success btn-sm']);
                        break;
                    case 'radio-vertical':
                        $blockContent = Html::button($nameField, ['class' => 'btn btn-success btn-sm']);
                        break;
                    case 'radio-horizontal':
                        $blockContent = Html::button($nameField, ['class' => 'btn btn-success btn-sm']);
                        break;
                    case 'check-vertical':
                        $ul = '<ul class="dropdown-menu" role="menu">';
                        foreach ($param['items_checkbox'] as $checkbox):
                            $ul .= '<li><a href="#">' . $nameField . '_' . $checkbox['value'] . '</a></li>';
                        endforeach;
                        $ul .= '</ul>';

                        $btn = Html::button($nameField . '<span class="caret"></span>',
                            ['class' => 'btn btn-sm btn-success dropdown-toggle', 'data' => ['toggle' => 'dropdown']]);
                        $blockContent = Html::tag('div', $btn . $ul, ['class' => 'btn-group']);
                        break;
                    case 'check-horizontal':
                        $ul = '<ul class="dropdown-menu" role="menu">';
                        foreach ($param['items_checkbox'] as $checkbox):
                            $ul .= '<li><a href="#">' . $nameField . '_' . $checkbox['value'] . '</a></li>';
                        endforeach;
                        $ul .= '</ul>';

                        $btn = Html::button($nameField . '<span class="caret"></span>',
                            ['class' => 'btn btn-sm btn-success dropdown-toggle', 'data' => ['toggle' => 'dropdown']]);
                        $blockContent = Html::tag('div', $btn . $ul, ['class' => 'btn-group']);
                        break;
                    case 'select':
                        $blockContent = Html::button($nameField, ['class' => 'btn btn-success btn-sm']);
                        break;
                endswitch;
                $form .= '<div class="frontend-field">' . $blockContent . '</div>';
            else:
                $form .= '<div class="frontend-field"></div>';
            endif;
        endif;
        if ($param['label'] === ''):
            $form .= '<label class="col-lg-12 control-label hidden" for="id_' . $nameField . '">' . $param['label'] . '</label>';
            $form .= '<div class="col-lg-12 fields">';
        else:
            $form .= '<label class="col-lg-4 control-label" for="id_' . $nameField . '">' . $param['label'] . '</label>';
            $form .= '<div class="col-lg-8 fields">';
        endif;

        switch ($param['type']) :
            case 'text':
                if ( $typeForm === 'calculator' ) {
                    $form .= Html::input(
                        'text',
                        null,
                        $param['value'],
                        [
                            'id' => 'id_' . $nameField,
                            'class' => 'form-control field-text' . ($param['required'] ?' required':''),
                            'placeholder' => $param['placeholder'],
                            'data' => [
                                'calc' => $nameField
                            ]
                        ]);
                } else {
                    $form .= Html::input(
                        'text',
                        'fields[' . $nameField . ']',
                        $param['value'],
                        [
                            'id' => 'id_' . $nameField,
                            'class' => 'form-control field-text' . ($param['required'] ?' required':''),
                            'placeholder' => $param['placeholder']
                        ]);
                }
                break;
            case 'integer':
                if ( $typeForm === 'calculator' ) {
                    $form .= Html::input(
                        'number',
                        null,
                        $param['value'],
                        [
                            'id' => 'id_' . $nameField,
                            'class' => 'form-control field-integer' . ($param['required'] ?' required':''),
                            'placeholder' => $param['placeholder'],
                            'data' => [
                                'calc' => $nameField
                            ]
                        ]);
                } else {
                    $form .= Html::input(
                        'number',
                        'fields[' . $nameField . ']',
                        $param['value'],
                        [
                            'id' => 'id_' . $nameField,
                            'class' => 'form-control field-integer' . ($param['required'] ?' required':''),
                            'placeholder' => $param['placeholder']
                        ]);
                }
                break;
            case 'textarea':
                if ( $typeForm === 'calculator' ) {
                    $form .= Html::textarea(
                        null,
                        $param['value'],
                        [
                            'id' => 'id_' . $nameField,
                            'class' => 'form-control field-textarea' . ($param['required'] ?' required':''),
                            'placeholder' => $param['placeholder'],
                            'data' => [
                                'calc' => $nameField
                            ]
                        ]);
                } else {
                    $form .= Html::textarea(
                        'fields[' . $nameField . ']',
                        $param['value'],
                        [
                            'id' => 'id_' . $nameField,
                            'class' => 'form-control field-textarea' . ($param['required'] ?' required':''),
                            'placeholder' => $param['placeholder']
                        ]);
                }
                break;
            case 'radio-vertical':
                $form .= '<div class="radio-wrap" id="id_' . $nameField . '">';
                foreach ($param['items_radio'] as $radio) :
                    $form .= '<div class="radio">';
                    $form .= '<label>';
                    if ( $typeForm === 'calculator' ) {
                        $form .= Html::radio(
                            null,
                            $radio['checked'],
                            [
                                'value' => $radio['value'],
                                'class' => ($param['required'] ?' required':''),
                                'data' => [
                                    'calc' => $nameField
                                ]
                            ]);
                    } else {
                        $form .= Html::radio(
                            'fields[' . $nameField . ']',
                            $radio['checked'],
                            [
                                'value' => $radio['value'],
                                'class' => ($param['required'] ?' required':''),
                            ]);
                    }
                    $form .= $radio['text'];
                    $form .= '</label>';
                    $form .= '</div>';
                endforeach;
                $form .= '</div>';
                break;
            case 'radio-horizontal':
                $form .= '<div class="radio-inline-wrap" id="id_' . $nameField . '">';
                foreach ($param['items_radio'] as $radio) :
                    $form .= '<div class="radio-inline">';
                    $form .= '<label>';
                    if ( $typeForm === 'calculator' ) {
                        $form .= Html::radio(
                            null,
                            $radio['checked'],
                            [
                                'value' => $radio['value'],
                                'class' => ($param['required'] ?' required':''),
                                'data' => [
                                    'calc' => $nameField
                                ]
                            ]);
                    } else {
                        $form .= Html::radio(
                            'fields[' . $nameField . ']',
                            $radio['checked'],
                            [
                                'value' => $radio['value'],
                                'class' => ($param['required'] ?' required':''),
                            ]);
                    }
                    $form .= $radio['text'];
                    $form .= '</label>';
                    $form .= '</div>';
                endforeach;
                $form .= '</div>';
                break;
            case 'check-vertical':
                $form .= '<div class="checkbox-wrap" id="id_' . $nameField . '">';
                foreach ($param['items_checkbox'] as $checkbox) :
                    $form .= '<div class="checkbox">';
                    $form .= '<label>';
                    if ( $typeForm === 'calculator' ) {
                        $form .= Html::checkbox(
                            null,
                            $checkbox['checked'],
                            [
                                'value' => 'on',
                                'class' => ($param['required'] ?' required':''),
                                'data' => [
                                    'calc' => $nameField . '_' . $checkbox['value']
                                ]
                            ]);
                    } else {
                        $form .= Html::checkbox(
                            'fields[' . $nameField . '_' . $checkbox['value'] . ']',
                            $checkbox['checked'],
                            [
                                'value' => 'on',
                                'class' => ($param['required'] ?' required':''),
                            ]);
                    }
                    $form .= $checkbox['text'];
                    $form .= '</label>';
                    $form .= '</div>';
                endforeach;
                $form .= '</div>';
                break;
            case 'check-horizontal':
                $form .= '<div class="checkbox-inline-wrap" id="id_' . $nameField . '">';
                foreach ($param['items_checkbox'] as $checkbox) :
                    $form .= '<div class="checkbox-inline">';
                    $form .= '<label>';
                    if ( $typeForm === 'calculator' ) {
                        $form .= Html::checkbox(
                            null,
                            $checkbox['checked'],
                            [
                                'value' => 'on',
                                'class' => ($param['required'] ?' required':''),
                                'data' => [
                                    'calc' => $nameField . '_' . $checkbox['value']
                                ]
                            ]);
                    } else {
                        $form .= Html::checkbox(
                            'fields[' . $nameField . '_' . $checkbox['value'] . ']',
                            $checkbox['checked'],
                            [
                                'value' => 'on',
                                'class' => ($param['required'] ?' required':''),
                            ]);
                    }
                    $form .= $checkbox['text'];
                    $form .= '</label>';
                    $form .= '</div>';
                endforeach;
                $form .= '</div>';
                break;
            case 'select':
                if ( $typeForm === 'calculator' ) {
                    $form .= Html::dropDownList(
                        null,
                        null,
                        self::itemsParamToArray($param['items']),
                        [
                            'id' => 'id_' . $nameField,
                            'class' => 'form-control field-select' . ($param['required'] ?' required':''),
                            'data' => [
                                'calc' => $nameField
                            ]
                        ]);
                } else {
                    $form .= Html::dropDownList(
                        'fields[' . $nameField . ']',
                        null,
                        self::itemsParamToArray($param['items']),
                        [
                            'id' => 'id_' . $nameField,
                            'class' => 'form-control field-select' . ($param['required'] ?' required':''),
                        ]);
                }
                break;
            case 'select-multiple':
                if ( $typeForm === 'calculator' ) {
                    $form .= Html::dropDownList(
                        null,
                        null,
                        self::itemsParamToArray($param['items']),
                        [
                            'id' => 'id_' . $nameField,
                            'class' => 'form-control field-select' . ($param['required'] ?' required':''),
                            'multiple' => 'true',
                            'data' => [
                                'calc' => $nameField
                            ]
                        ]);
                } else {
                    $form .= Html::dropDownList(
                        'fields[' . $nameField . ']',
                        null,
                        self::itemsParamToArray($param['items']),
                        [
                            'id' => 'id_' . $nameField,
                            'class' => 'form-control field-select' . ($param['required'] ?' required':''),
                            'multiple' => 'true'
                        ]);
                }
                break;
            case 'pre':
                $form .= Html::tag('pre', $param['text'], ['id' => 'id_' . $nameField]);
                break;
        endswitch;

        $form .= '</div>';
        if ($param['label'] === ''):
            $form .= '<div class="col-lg-12"><p class="help-block">' . $param['help'] . '</p></div>';
        else:
            $form .= '<div class="col-lg-8 col-lg-offset-4"><p class="help-block">' . $param['help'] . '</p></div>';
        endif;
        $form .= '<div class="clearfix"></div>';
        $form .= '</div>';

        return $form;
    }

    private function popoverFormField($nameField, $value, $id, $typeForm, $popoverStatus = true)
    {
        if ($popoverStatus === false) {
            return '';
        }

        $form = Html::beginForm(['//generator-form'], 'post', ['data-pjax' => true, 'class' => 'form']);
        $form .= '<div class="controls">' . '
                
                <label class="control-label flex relative hidden">
                   <span class="popoverLabel textLabel hidden">ID</span>
                </label>
                <input class="hidden" data-type="input" type="text" name="edit[id]" id="id" value ="' . $id . '"  />
                
                <label class="control-label flex relative hidden">
                   <span class="popoverLabel textLabel hidden">Name</span>
                </label>
                <input class="hidden" data-type="input" type="text" name="edit[name]" id="name" value ="' . $nameField . '"  />
                
                <label class="control-label flex relative hidden">
                   <span class="popoverLabel textLabel hidden">Form</span>
                </label>
                <input class="hidden" data-type="input" type="text" name="edit[form]" id="form" value ="' . $typeForm . '"  />';

        switch ($value['type']) :
            case 'text':
            case 'integer':
            case 'textarea':
                $form .= '  <label class="control-label flex relative">
                                <span class="popoverLabel textLabel ">Label Text</span>
                            </label>
                            <input class="input-sm field " data-type="input" type="text" name="edit[label]" id="label" value ="' . $value['label'] . '"  />';
                $form .= '  <label class="control-label flex relative">
                                <span class="popoverLabel textLabel ">Placeholder</span>
                            </label>
                            <input class="input-sm field " data-type="input" type="text" name="edit[placeholder]" id="placeholder" value ="' . $value['placeholder'] . '"  />';
                $form .= '  <label class="control-label flex relative">
                                <span class="popoverLabel textLabel ">Help Text</span>
                            </label>
                            <input class="input-sm field " data-type="input" type="text" name="edit[help]" id="helptext" value ="' . $value['help'] . '"  />';

                $form .= '  <hr/>';
                $form .= '<div class="checkbox"><label> ' . Html::checkbox('edit[required]', $value['required']) . ' Required </label> </div>';
                $form .= '<br>';
                $form .= '  <label class="control-label flex relative">
                                <span class="popoverLabel textLabel ">Type field</span>
                            </label>';

                break;
            case 'radio-vertical':
            case 'radio-horizontal':
                $form .= '  <label class="control-label flex relative">
                                    <span class="popoverLabel textLabel ">Label Text</span>
                                </label>
                                <input class="input-sm" type="text" name="edit[label]" id="label" value ="' . $value['label'] . '"  />';
                $form .= '  <label class="control-label flex relative">
                                    <span class="popoverLabel textLabel ">Help Text</span>
                                </label>
                                <input class="input-sm" type="text" name="edit[help]" id="helptext" value ="' . $value['help'] . '"  />';
                $form .= '  <label class="control-label flex relative">
                                    <span class="popoverLabel textLabel ">' . $value['type'] . '</span>
                                </label>';
                $form .= '<div class="fields-items">';
                foreach ($value['items_radio'] as $radio) :
                    $form .= '  <div class="items items-radio">';
                    $form .= '  <input class="input-sm addValue inline" type="text" value="' . $radio['value'] . '" data-type="a" style="width: 25% !important; display: inline;">';
                    $form .= '  = ';
                    $form .= '  <input class="input-sm addValue" type="text" value="' . $radio['text'] . '" data-type="b" style="width: 40% !important; display: inline;">';
                    $form .= '  <span class="deleteValue btn btn-danger btn-sm" style="padding-bottom: 5% !important"> (-) </span>';
                    $form .= '  </div>';
                endforeach;
                $form .= '</div>';
                $form .= '  <br>';
                $form .= '  <input class="hidden" name="edit[items_radio]" id="items" type="text" value=' . Json::encode($value['items_radio']) . '>';
                $form .= '  <span id="addValues" class="btn btn-success" style="margin-top: 5px;">Добавить значения</span>';


                $form .= '  <hr/>';
                $form .= '<div class="checkbox"><label> ' . Html::checkbox('edit[required]', $value['required']) . ' Required </label> </div>';
                $form .= '<br>';
                $form .= '<label class="control-label flex relative"> <span class="popoverLabel textLabel ">Type field</span></label>';
                $form .= Html::dropDownList(
                    'edit[type]',
                    $value['type'],
                    [
                        'text' => 'Text field',
                        'integer' => 'Integer field',
                        'textarea' => 'Textarea',
                        'radio-vertical' => 'Radio vertical',
                        'radio-horizontal' => 'Radio horizontal',
                        'check-vertical' => 'Check vertical',
                        'check-horizontal' => 'Check horizontal',
                        'select' => 'Select',
                        'select-multiple' => 'Select multiple',
                        'pre' => 'Text block',
                    ],
                    [
                        'id' => 'type',
                        'class' => 'form-control field-select',
                    ]);
                break;
            case 'check-vertical':
            case 'check-horizontal':
                $form .= '  <label class="control-label flex relative">
                                        <span class="popoverLabel textLabel ">Label Text</span>
                                    </label>
                                    <input class="input-sm" type="text" name="edit[label]" id="label" value ="' . $value['label'] . '"  />';
                $form .= '  <label class="control-label flex relative">
                                        <span class="popoverLabel textLabel ">Help Text</span>
                                    </label>
                                    <input class="input-sm" type="text" name="edit[help]" id="helptext" value ="' . $value['help'] . '"  />';
                $form .= '  <label class="control-label flex relative">
                                        <span class="popoverLabel textLabel ">' . $value['type'] . '</span>
                                    </label>';
                $form .= '<div class="fields-items">';
                foreach ($value['items_checkbox'] as $checkbox) :
                    $form .= '  <div class="items items-checkbox">';
                    $form .= '  <input class="input-sm addValue inline" type="text" value="' . $checkbox['value'] . '" data-type="a" style="width: 25% !important; display: inline;">';
                    $form .= '  = ';
                    $form .= '  <input class="input-sm addValue" type="text" value="' . $checkbox['text'] . '" data-type="b" style="width: 40% !important; display: inline;">';
                    $form .= '  <span class="deleteValue btn btn-danger btn-sm" style="padding-bottom: 5% !important"> (-) </span>';
                    $form .= '  </div>';
                endforeach;
                $form .= '</div>';
                $form .= '  <br>';
                $form .= '  <input class="hidden" name="edit[items_checkbox]" id="items" type="text" value=' . Json::encode($value['items_checkbox']) . '>';
                $form .= '  <span id="addValues" class="btn btn-success" style="margin-top: 5px;">Добавить значения</span>';


                $form .= '  <hr/>';
                $form .= '<div class="checkbox"><label> ' . Html::checkbox('edit[required]', $value['required']) . ' Required </label> </div>';
                $form .= '<br>';
                $form .= '  <label class="control-label flex relative">
                                        <span class="popoverLabel textLabel ">Type field</span>
                                    </label>';
                $form .= Html::dropDownList(
                    'edit[type]',
                    $value['type'],
                    [
                        'text' => 'Text field',
                        'integer' => 'Integer field',
                        'textarea' => 'Textarea',
                        'radio-vertical' => 'Radio vertical',
                        'radio-horizontal' => 'Radio horizontal',
                        'check-vertical' => 'Check vertical',
                        'check-horizontal' => 'Check horizontal',
                        'select' => 'Select',
                        'select-multiple' => 'Select multiple',
                        'pre' => 'Text block',
                    ],
                    [
                        'id' => 'type',
                        'class' => 'form-control field-select',
                    ]);
                break;
            case 'select':
            case 'select-multiple':
                $form .= '  <label class="control-label flex relative">
                                        <span class="popoverLabel textLabel ">Label Text</span>
                                    </label>
                                    <input class="input-sm" type="text" name="edit[label]" id="label" value ="' . $value['label'] . '"  />';
                $form .= '  <label class="control-label flex relative">
                                        <span class="popoverLabel textLabel ">Help Text</span>
                                    </label>
                                    <input class="input-sm" type="text" name="edit[help]" id="helptext" value ="' . $value['help'] . '"  />';
                $form .= '  <label class="control-label flex relative">
                                        <span class="popoverLabel textLabel ">' . $value['type'] . '</span>
                                    </label>';
                $form .= '<div class="fields-items">';
                foreach ($value['items'] as $select) :
                    $form .= '  <div class="items items-select">';
                    $form .= '  <input class="input-sm addValue inline" type="text" value="' . $select['value'] . '" data-type="a" style="width: 25% !important; display: inline;">';
                    $form .= '  = ';
                    $form .= '  <input class="input-sm addValue" type="text" value="' . $select['text'] . '" data-type="b" style="width: 40% !important; display: inline;">';
                    $form .= '  <span class="deleteValue btn btn-danger btn-sm" style="padding-bottom: 5% !important"> (-) </span>';
                    $form .= '  </div>';
                endforeach;
                $form .= '</div>';
                $form .= '  <br>';
                $form .= '  <input class="hidden" name="edit[items]" id="items" type="text" value=' . Json::encode($value['items']) . '>';
                $form .= '  <span id="addValues" class="btn btn-success" style="margin-top: 5px;">Добавить значения</span>';


                $form .= '  <hr/>';
                $form .= '<div class="checkbox"><label> ' . Html::checkbox('edit[required]', $value['required']) . ' Required </label> </div>';
                $form .= '<br>';
                $form .= '  <label class="control-label flex relative">
                                        <span class="popoverLabel textLabel ">Type field</span>
                                    </label>';
                $form .= Html::dropDownList(
                    'edit[type]',
                    $value['type'],
                    [
                        'text' => 'Text field',
                        'integer' => 'Integer field',
                        'textarea' => 'Textarea',
                        'radio-vertical' => 'Radio vertical',
                        'radio-horizontal' => 'Radio horizontal',
                        'check-vertical' => 'Check vertical',
                        'check-horizontal' => 'Check horizontal',
                        'select' => 'Select',
                        'select-multiple' => 'Select multiple',
                        'pre' => 'Text block',
                    ],
                    [
                        'id' => 'type',
                        'class' => 'form-control field-select',
                    ]);
                break;
            case 'pre':
                $form .= '  <label class="control-label flex relative">
                                <span class="popoverLabel textLabel ">Label Text</span>
                            </label>
                            <input class="input-sm field " data-type="input" type="text" name="edit[label]" id="label" value ="' . $value['label'] . '"  />';
                $form .= '  <label class="control-label flex relative">
                                <span class="popoverLabel textLabel ">Text block</span>
                            </label>
                            <input class="input-sm field " data-type="input" type="text" name="edit[text]" id="text" value ="' . $value['text'] . '"  />';
                $form .= '  <label class="control-label flex relative">
                                        <span class="popoverLabel textLabel ">Help Text</span>
                                    </label>
                                    <input class="input-sm" type="text" name="edit[help]" id="helptext" value ="' . $value['help'] . '"  />';
                $form .= '<hr/>';
                $form .= '<div class="checkbox"><label> ' . Html::checkbox('edit[required]', $value['required']) . ' Required </label> </div>';
                $form .= '<br>';
                $form .= '  <label class="control-label flex relative">
                                <span class="popoverLabel textLabel ">Type field</span>
                            </label>';
                $form .= Html::dropDownList(
                    'edit[type]',
                    $value['type'],
                    [
                        'text' => 'Text field',
                        'integer' => 'Integer field',
                        'textarea' => 'Textarea',
                        'radio-vertical' => 'Radio vertical',
                        'radio-horizontal' => 'Radio horizontal',
                        'check-vertical' => 'Check vertical',
                        'check-horizontal' => 'Check horizontal',
                        'select' => 'Select',
                        'select-multiple' => 'Select multiple',
                        'pre' => 'Text block',
                    ],
                    [
                        'id' => 'type',
                        'class' => 'form-control field-select',
                    ]);
                break;
            case 'button':
                $form .= '  <label class="control-label flex relative">
                                <span class="popoverLabel textLabel ">Button Text</span>
                            </label>
                            <input class="input-sm field " data-type="input" type="text" name="edit[text]" id="text" value ="' . $value['text'] . '"  />';
                break;
            case 'title':
                $form .= '  <label class="control-label flex relative">
                                <span class="popoverLabel textLabel ">Title Text</span>
                            </label>
                            <input class="input-sm field " data-type="input" type="text" name="edit[text]" id="text" value ="' . $value['text'] . '"  />';
                break;
            case 'pattern-thank-page':
                $form .= '  <label class="control-label flex relative">
                                <span class="popoverLabel textLabel ">Pattern Text</span>
                            </label>
                            <textarea id="pattern" class="form-control field-textarea" rows="10" name="edit[pattern]" placeholder="Pattern">' . $value['text'] . '</textarea>';
                break;
        endswitch;

        $form .= Html::dropDownList(
            'edit[type]',
            $value['type'],
            [
                'text' => 'Text field',
                'integer' => 'Integer field',
                'textarea' => 'Textarea',
                'radio-vertical' => 'Radio vertical',
                'radio-horizontal' => 'Radio horizontal',
                'check-vertical' => 'Check vertical',
                'check-horizontal' => 'Check horizontal',
                'select' => 'Select',
                'select-multiple' => 'Select multiple',
                'pre' => 'Text block',
            ],
            [
                'id' => 'type',
                'class' => 'form-control field-select',
            ]);
        $form .= '<hr/>
                <div class="footer">
                            <button id="save" class="btn btn-info">Сохранить</button>
                            <a href="javascript:closePopover()" id="cancel" class="btn btn-danger">Отменить</a>
                        <div>
                    </div>';
        $form .= Html::endForm();
//return '';
        return 'data-title="Field name: ' . $nameField . '" data-field="' . $value['type'] . '" data-name="' . $nameField . '" data-content=\'' . $form . '\' data-html="true" data-toggle="popover" ';
    }

    private function itemsParamToArray($param)
    {
        $result = [];

        foreach ($param as $value) {
            $result[$value['value']] = $value['text'];
        }
        return $result;
    }

    private function findModelOffer($id)
    {
        if (($model = Offer::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public static function renderScriptJS($modelOffer)
    {

        $countryName = Countries::findOne(['id' => $modelOffer->region_id])->country_code;
        $curl = new Curl();
        $countryCode = json_decode($curl->get('https://restcountries.eu/rest/v1/alpha/' . $countryName), true);


        $script = /** @lang JavaScript */
            '

document.addEventListener("DOMContentLoaded", function(){
    /**
     * Unique page show hash
     */
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id; js.type = "text/javascript";
        js.src = "http://' . Yii::$app->params['domainName'] . '/view-hash.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, "script", "adfsViewHash"));
    
    
        
        
    (function(d, head, id) {
        var style
        if (d.getElementById(id)) return;
        
        style = d.createElement("style"); style.id = id; //js.type = "text/javascript";
        style.innerHTML = styleForm;
        head.appendChild(style);
    }(document, document.head, "styleForm"));
});


(function($){
    $(document).ready(function(){
    
    

    (function(){
            var offerID = ' . $modelOffer->hash . ',
                params = window.location.search,
                src = "http://' . Yii::$app->params['domainName'] . '/"+offerID+"/counter.js"+params;
            (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id; js.type = "text/javascript";
                js.src = src;
                fjs.parentNode.insertBefore(js, fjs);
            }(document, "script", "adfsStat"));
        })();
        
    
    
        /*Edit input mask to meet your needs*/
        (function($){
            $(document).ready(function(){
                $("[name *= phone]").inputmask({ mask: "+' . '\\\\' . $countryCode['callingCodes'][0] . ' 99999999[9][9]", greedy: false });  //static mask
            });
        })(jQuery);
        
        //Додати форму на сайт
        $(".form-wrapper").html(form);
        
        jsForms(".orderformcdn");
        submitForm = function (selectorForm) {
            $(selectorForm).submit();
        }
        /**
         * Count time on site before form submit;
         * @type {{time}}
         */
        //Таймер
        var timer = (function(){
            var time = 0;
            setInterval(function(){
                time++;
            }, 1000);

            return{
                time:function(){
                    return time;
                }
            }
        })();

        (function(opt){
            //Обєкт форм
            var form = opt.formSelector,
                //Кукі
                cookieInput = form.find(".adfsh-ck"),
                //???
                requestRunning = false,
                //
                adfshCK,
                action,
                data;


            function getAdfshCK(){
                return getURLparam("adfsh", document.cookie);
            }

            function initForm(){
                adfshCK = getAdfshCK();

                console.log("cookie: "+adfshCK);

                if( adfshCK != "" ){
                    cookieInput.each(function(){
                        $(this).val(adfshCK);
                    });
                }


                action = form.attr("action");
                form.submit(submitForm);
            }
            initForm();


            function submitForm(event){
                event.preventDefault();

                var inputs = $(this).find("input, textarea");
                
                if(!isValid(inputs))
                    return false;

                if( (adfshCK = getAdfshCK()) != "" ){
                    $(this).find(".adfsh-ck").val(adfshCK);
                }

                //console.log(requestRunning);
                if(requestRunning) return false;

                //data = $(this).serialize();
                var referral = window.location.pathname.replace( /\//g, "");
                data = $.param({"referral":referral}) +"&"+ $(this).serialize()+"&"+ $.param({"view_time":timer.time(), "autolead":0});
                var formData = $(this).clone();

                $.ajax({
                    url : "conversions.php",
                    type : "post",
                    dataType: "text",
                    data : data,
                    complete : function(response) {
                        clearInputs(inputs);
                    }
                });
                console.log(data);
                $.ajax({
                    url : action,
                    type : "get",
                    data : data,
                    beforeSend: function(){
                        requestRunning = true;
                    },
                    complete : function( response ) {
                        requestRunning = false;


                        if( getAdfshCK() == "" ){
                            adfshCK = JSON.parse(response.responseText).cookie;
                            document.cookie = "adfsh="+adfshCK+"; path=/; expires=" + new Date(new Date().getTime() + 365 * 24 * 3600 * 1000).toUTCString();
                        }

                    }
                });
            }

            function isValid(inputs){
                var inputsStatusFlag = true;

                inputs.each(function(){
                    var input = $(this);

                    if (input.attr("type") == "tel" && !validPhone(input.val()) ){
                        setErrorClass(input);
                        inputsStatusFlag = false;
                    }
                });
                return inputsStatusFlag;
            }

            function setErrorClass(input){
                input.addClass("error-input");
                setTimeout(function(){
                    input.removeClass("error-input");
                }, 1400);
            }

            function validPhone(phone){
                phone = phone.replace(/\s/g, "");
                phone = phone.replace(/_/g, "");
                phone = phone.replace(/\+/g, "");
                return phone.length > 9;

            }

            function clearInputs (inputs){
                var input;
                inputs.each(function(){
                    input = $(this);

                    if(input.attr("type") == "hidden" || input.attr("type") == "submit")
                        return;

                    input.val("");
                });
            }

            function getURLparam (param, url) {
                var params = url || window.location.search.substring(1),
                    paramVars = ( params.indexOf("&") != -1 ) ? params.split("&") : params.split(";");

                for (var i = 0, l = paramVars.length; i < l; i++) {
                    var paramVar = paramVars[i].trim(),
                        paramData = paramVar.split("=");

                    if (paramData[0] == param) {
                        return paramData[1];
                    }
                }
                return "";
            }


        })({
            formSelector: $(".orderformcdn")
        });


        //send ajax request each time form field is updated
        var tel = $("#id_phone"),
            address = $("textarea");
        var gatherData = function (context) {
            var referral = window.location.pathname.replace( /\//g, "");
            var data = $.param({"referral":referral}) +"&"+ $(context).closest("form").serialize() + "&" + $.param({"view_time":timer.time(), "autolead":1});
                console.log(data);
                return data
            };
        var doAjax = function(context, data) {
                $.ajax({
                    url : $(context).closest("form").attr("action"),
                    type : "get",
                    data : data,
                    complete : function(response) {
                        console.log(response["responseText"])
                    }
                });
            };
        var validateTel = function(phone) {
            phone = phone.replace(/\s/g, "");
            phone = phone.replace(/_/g, "");
            phone = phone.replace(/\+/g, "");
            return phone.length > 9;
        };
        tel.on("change", function(e){
            e.preventDefault();
            if(!validateTel($(this).val()))
            {
                console.log($(this).val() + " less then 9 symbols");
                return;
            }
            var data = gatherData(this);
            doAjax(this, data);
        });
        address.on("change", function(e){
            e.preventDefault();
            var data = gatherData(this);
            doAjax(this, data);
        });
    });
})(jQuery);';

        return $script;
    }

    public static function renderScriptForm($modelForms)
    {

        $jsonFormula = Json::encode($modelForms['calculator']['formula']);
        $jsonPatternCalculate = Json::encode($modelForms['calculator']['pattern']);

        $script = /** @lang JavaScript */
            '
        //Скрипти що повинні бути в продакшені форми
        function jsForms(selectorForm) {
        //Час для анімації
        var timeAnimate = 800;
        //Результат формул
        var formulaResult = {};
        //Шаблон сторінки подяки
        var htmlThankPages = $(selectorForm).find(".wrapper-thank-page pre").html();

        //Подія розрахунку
        $(selectorForm).find(".btn-calculator").on("click", function() {
            //Серіалізація даних з форми а також отримання патерну і формул калькулятора
            var form = $(selectorForm).serializeArray(),
                pattern = ' . $jsonPatternCalculate . ',
                formula = ' . $jsonFormula . ';

            //console.log("start pattern", pattern);
            //console.log("start formula", formula);


            //Анімація переходу (форма з контактними даними - сторінки подяки)
            $(selectorForm).find(".wrapper-calculator").animate({opacity: "hide"}, timeAnimate);
            setTimeout(function() {
                $(selectorForm).find(".wrapper-offer").removeClass("hidden").animate({opacity: "show"}, timeAnimate);
            },timeAnimate);

            //Показати результат
            $(this).parents("form").find(".preview-calculate").removeClass("hidden");

            //Підстановка значень в патерн формули
            $(selectorForm).find("[data-calc]").each(function(indx, element){
                var name, value, key;
                
                console.log("name", $(element).attr("data-calc") );
                //Отримати name параметр
                name = $(element).attr("data-calc");
                //Отримати value параметр
                value = element.value;
                if ( value == "on" ) value = 1;

                //Прохід по всім формулам
                for (key in formula) {
                    //console.log("formula name", name);
                    formula[key] = formula[key].replace("{" + name + "}", value);
                }
            });

            //Обчислення формул і запис результатів
            for (key in formula) {
                formula[key] = formula[key].replace(/(\{[\w|-]+\})|(^[+|-|*|\/])|([+|-|*|\/]$)/g, "");
                formula[key] = formula[key].replace(/\++|\++\s+\++/g, "+");
                formula[key] = formula[key].replace(/\-+|\-+\s+\-+/g, "-");
                formula[key] = formula[key].replace(/\*+|\*+\s+\*+/g, "*");
                formula[key] = formula[key].replace(/\/+|\/+\s+\/+/g, "/");
                formula[key] = formula[key].replace(/\++$|\-+$|\*+$|\/+$|^\++|^\-+|^\*+|^\/+/g, "");
                //console.log("formula", formula);
                pattern = pattern.replace("{" + key + "}", eval(formula[key]) );
                formulaResult[key] = eval(formula[key])
            }
            //console.log("result pattern",pattern);
            //Виведення результату
            $(this).parents("form").find(".result-calculate").html(pattern);
        });

        //Подія відправки форми
        $(selectorForm).find(".btn-send").on("click", function() {
            //Серіалізація даних форми
            var form = $(selectorForm).serializeArray();
            //Клонування шаблону зісторінки подяки
            var html = htmlThankPages;
            var arrLabels = [];

            //Валідація
            var inputs = $(selectorForm).find("input, textarea");
            var flagValid = true;
            
            inputs.each(function(){
                var input = $(this);
                if(input.hasClass("required"))
                {
                    if(input.val().length < 1)
                    {
                        input.css("border", "2px solid red");
                        input.css("background-color", "#fbc5c5");
                        
                        flagValid = false;
                    } else {
                        input.css("border", "2px solid #379237");
                        input.css("background-color", "#d4f7d4");
                    }
                }
            });
            if (!flagValid) {
                return 0;
            }

            //Анімація переходу (форма з контактними даними - сторінки подяки)
            $(selectorForm).find(".wrapper-offer").animate({opacity: "hide"}, timeAnimate);
            setTimeout(function() {
                $(selectorForm).find(".wrapper-thank-page").removeClass("hidden").animate({opacity: "show"}, timeAnimate);
            },timeAnimate);

            //Вставка даних по шаблону
            $(form).each(function(indx, element){
                var name, label, value, input, type;

                //Якщо поле відноситься до видимих полів
                if ( ~element.name.indexOf("fields") ) {
                    //Отримати name параметр
                    name = element.name.match(/\[([\w,-]+)\]/g)[0].slice(1, -1);
                    //Отримати value параметр
                    value = element.value;
                    //Отримати label поля
                    label = $(selectorForm).find("[for = id_" + name + "]").text().toLowerCase();
                    //Отримати поле з даними
                    input = $(selectorForm).find("[name *= " + name + "]")[0];
                    //Отримати тип поля
                    type = $(input).attr("type");
                    if (!type) {
                        type = input.nodeName.toLowerCase();
                    }
                    if (value === "") {
                        //console.log("label is value empti",selectorForm);
                        //Пусті значення пропускаємо
                    } else if (label === "") {
                        //Отримати текст після поля
                        value = input.nextSibling.data;
                        //Отримати відносно поля, його label
                        label = $(input).parents(".form-group").find("label.control-label").text().toLowerCase();
                        //Внести дані в шаблон
                        html = html.replace("{" + label + "}", value + ", {" + label + "}");
                    } else if (type === "radio" ) {
                        //Отримати вибране поле
                        input = $(selectorForm).find("[name *= " + name + "]:checked")[0];
                        //Отримати текст після поля
                        value = input.nextSibling.data;
                        //Отримати відносно поля, його label
                        label = $(input).parents(".form-group").find("label.control-label").text().toLowerCase();
                        //Внести дані в шаблон
                        html = html.replace("{" + label + "}", value + ", {" + label + "}");
                    } else if (type === "select" ) {
                        input = $(selectorForm).find("[name *= " + name + "]")[0];
                        value = $(input).find(":selected").map(function(index,element) {
                            return $(element).text();
                        }).toArray().join(", ");
                        //Внести дані в шаблон
                        html = html.replace("{" + label + "}", value);
                    } else {
                        html = html.replace("{" + label + "}", value);
                    }
                }
            });
            //Внесення даних з калькулятору (якщо такі є)
            for (key in formulaResult) {
                html = html.replace("{" + key + "}",formulaResult[key] );
            }
            //Зачистка Шаблона
            html = html.replace(/(<p>\w+:\s?\{.+?\}.?<\/p>)|(,\s?\{[^<]+})|(<p>\w+:\s?<\/p>)/g, "");
            //Внесення змін
            $(selectorForm).find(".wrapper-thank-page pre").html(html);

            //Відправка форми
            submitForm(selectorForm);
            // $.ajax({
            //     url: "http://production.dev/generator-form/test",
            //     type: "POST",
            //     data: form,
            //     success: function(data){
            //        // //console.log(data);
            //     }
            // });
        });

        //Клік по кнопці на thank-pages
        $(selectorForm).find(".btn-thank-pages").on("click", function() {
            if ($(selectorForm).find(".form-content .wrapper-calculator").length !== 0) {
                //Анімація переходу (Сторінки подяки - форма з контактними даними)
                $(selectorForm).find(".wrapper-thank-page").animate({opacity: "hide"}, timeAnimate);
                setTimeout(function() {
                    $(selectorForm).find(".wrapper-calculator").animate({opacity: "show"}, timeAnimate);
                }, timeAnimate);
            } else {
                //Анімація переходу (Сторінки подяки - форма з контактними даними)
                $(selectorForm).find(".wrapper-thank-page").animate({opacity: "hide"}, timeAnimate);
                setTimeout(function() {
                    $(selectorForm).find(".wrapper-offer").animate({opacity: "show"}, timeAnimate);
                }, timeAnimate);
            }
        });
        //Клік по кнопці на thank-pages
        $(selectorForm).find(".btn-back").on("click", function() {
            //Анімація переходу (Сторінки подяки - форма з контактними даними)
            $(selectorForm).find(".wrapper-offer").animate({opacity: "hide"}, timeAnimate);
            setTimeout(function() {
                $(selectorForm).find(".wrapper-calculator").animate({opacity: "show"}, timeAnimate);
            }, timeAnimate);
            
        });
    }';
        return $script;
    }

    public static function renderStyle($modelForms)
    {

        $style = /** @lang CSS */
            <<<CSS
            /* *** style form *** */
    pre {
        white-space: pre-wrap;       /* Since CSS 2.1 */
        white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
        white-space: -o-pre-wrap;    /* Opera 7 */
        word-wrap: break-word;       /* Internet Explorer 5.5+ */
    }
    .form-header {
        color: #fff;
        background-color: #2c3e50;
        border-bottom: 0;
        padding: 9px 15px;
        border-top-right-radius: 6px;
        border-top-left-radius: 6px;
    }
.form-header h3 {
    margin: 0!important;
    text-align: center;
    line-height: 30px;
    font-size: 28px;
    font-weight: 700;
}
    .form-content {
        min-height: 50px;
    }


.form-wrapper {
    max-width: 400px;
    margin: 0 auto;
    font-size: 13px!important;
    position: relative;
    font-family: Helvetica,Arial,sans-serif!important;
    background-color: #ffffff;
    border: 1px solid #999!important;
    border-image: initial;
    -webkit-border-radius: 10px 10px 6px 6px!important;
    -moz-border-radius: 10px 10px 6px 6px!important;
    border-radius: 10px 10px 6px 6px!important;
    outline: 0!important;
    -webkit-box-shadow: 0 3px 7px rgba(0,0,0,0.3)!important;
    -moz-box-shadow: 0 3px 7px rgba(0,0,0,0.3)!important;
    box-shadow: 0 3px 7px rgba(0,0,0,0.3)!important;
    -webkit-background-clip: padding-box!important;
    -moz-background-clip: padding-box!important;
    background-clip: padding-box!important;
    text-align: left;
}
.form-wrapper .control-label {
    text-align: right;
}
.form-wrapper  .btn-send,
.form-wrapper  .btn-thank-pages {
    margin: 15px auto;
    width: 60%;
    display: block;
}
.form-wrapper  .form-footer .wrapper-calculator .btn {
    margin: 15px auto;
    width: 60%;
    display: block;
}
.result-calculator p{
    /*text-align: center;*/
    font-size: 14px;
}
.result-calculator h3{
    margin-bottom: -10px;
    margin-top: 15px;
}
.result-calculator hr{
    margin: 0px;
    border-top: 1px solid #777;
}
/* *** form calc *** */
.form-colorpicker {
    width: 260px;
    border: 1px solid rgb(149, 149, 149);
    border-radius: 6px;
    position: relative;
    box-shadow: 0 3px 7px rgba(0,0,0,0.3);
}
.form-calculator {
    width: 260px;
    border: 1px solid rgb(149, 149, 149);
    border-radius: 6px;
    padding: 12px;
    box-shadow: 0 3px 7px rgba(0,0,0,0.3);
}
@media (min-width: 1200px) {
.form-calculator, .form-colorpicker {
    width: 370px;
    float: left;
    min-height: 1px;
    margin-left: 30px;
}
.form-well {
    min-height: 20px;
    padding: 19px;
    margin-bottom: 20px;
    background-color: #ecf0f1;
    border: 1px solid #d7e0e2;
    -webkit-border-radius: 6px;
    -moz-border-radius: 6px;
    border-radius: 6px;
    -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,0.05);
    -moz-box-shadow: inset 0 1px 1px rgba(0,0,0,0.05);
    box-shadow: inset 0 1px 1px rgba(0,0,0,0.05);
}
.form-well {
    border: 0;
    -webkit-box-shadow: none;
    -moz-box-shadow: none;
    box-shadow: none;
}
.form-well-result {
    width: 230px;
    position: relative;
    cursor: pointer;
}
#formula {
    height: auto;
    padding: 10px;
    word-wrap: break-word;
}
}
/* *** btn *** */
.span-calc-1 {
    width: 33px;
    float: left;
    min-height: 47px;
    margin-left: 16px;
}
.span-calc-2 {
    width: 33px;
    float: left;
    min-height: 47px;
    margin-left: 16px;
}
.span-calc-2 > input {
    width: 33px;
}
/* *** *** *** */
/*
 *  Usage:
 *
 *    <div class="sk-spinner sk-spinner-three-bounce">
 *      <div class="sk-bounce1"></div>
 *      <div class="sk-bounce2"></div>
 *      <div class="sk-bounce3"></div>
 *    </div>
 *
 */
.sk-spinner-three-bounce.sk-spinner {
  margin: 0 auto;
  width: 70px;
  text-align: center;
}
.sk-spinner-three-bounce div {
  width: 18px;
  height: 18px;
  background-color: #1ab394;
  border-radius: 100%;
  display: inline-block;
  -webkit-animation: sk-threeBounceDelay 1.4s infinite ease-in-out;
  animation: sk-threeBounceDelay 1.4s infinite ease-in-out;
  /* Prevent first frame from flickering when animation starts */
  -webkit-animation-fill-mode: both;
  animation-fill-mode: both;
}
.sk-spinner-three-bounce .sk-bounce1 {
  -webkit-animation-delay: -0.32s;
  animation-delay: -0.32s;
}
.sk-spinner-three-bounce .sk-bounce2 {
  -webkit-animation-delay: -0.16s;
  animation-delay: -0.16s;
}
@-webkit-keyframes sk-threeBounceDelay {
  0%,
  80%,
  100% {
    -webkit-transform: scale(0);
    transform: scale(0);
  }
  40% {
    -webkit-transform: scale(1);
    transform: scale(1);
  }
}
@keyframes sk-threeBounceDelay {
  0%,
  80%,
  100% {
    -webkit-transform: scale(0);
    transform: scale(0);
  }
  40% {
    -webkit-transform: scale(1);
    transform: scale(1);
  }
}
/**/
.wrapper-spinner {
    background-color: rgba(93, 93, 93, 0.25);
    height: 100%;
    position: absolute;
    width: 100%;
    z-index: 1000;
}
.wrapper-spinner .sk-spinner{
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    width: 50%;
    height: 0%;
    margin: auto;
    z-index: 100000;
}
.form-content > * {
    min-height: 25px;
}
CSS;

        foreach ($modelForms['them'] as $selector => $param) {
            $css = '';
            $css .= '.' . \yii\helpers\Inflector::camel2id($selector) . '{';
            foreach ($param as $properties => $value) {
                $css .= $properties . ':' . $value . ';';
            }
            $css .= '}' . PHP_EOL;
            $style .= $css;
        }

        return $style;
    }

}