<?php
/** @var string $nameForm */
use yii\widgets\Pjax;

/** @var string $modelForm */
/** @var \tds\modules\genform\models\fields\ThemeFields $modelTheme */

?>
    <div id="wrapper" class="">
        <div id="sidebar" class="col-xs-4">
            <?= $this->render('_sidebar-index', [
                'listForms' =>$listForms,
            ]) ?>
        </div>
        <div id="content" class="col-xs-8">
            <?php Pjax::begin([
                'id' => 'form-container',
                'enablePushState' => false
            ]); ?>
            <div class="wrapper-spinner" style="display: none">
                <div class="sk-spinner sk-spinner-three-bounce">
                    <div class="sk-bounce1"></div>
                    <div class="sk-bounce2"></div>
                    <div class="sk-bounce3"></div>
                </div>
                <div class="clearfix"></div>
            </div>
            <?= $this->render('_content-preview-form', [
                'listPagesForm' => $listPagesForm,
                'form' => $form,
            ]) ?>

            <?php $this->registerJS(<<<'JS'

$("#select-list-pages").select2({
    minimumResultsForSearch: -1,
    placeholder: "Select page.."
});

$("#select-list-pages").change(function(e){
    var text = $(this).find('option:selected').text();
    $('.pages').css('display', 'none');
    $('.page-' + text).css('display', 'block');
  // console.log($(e).val());
  // console.log($('.page-' + text).css('display', 'none'));
});

JS
);?>
            <?php Pjax::end(); ?>
        </div>
    </div>
<?php
$url = \yii\helpers\Url::toRoute(['index']);
$this->registerJS(<<<JS
$('#menu li').click(function(e){
    if (e.toElement.tagName !== 'A' && e.toElement.tagName !== 'BUTTON') {
        $('#menu li').removeClass('selected');
        $(e.currentTarget).addClass('selected');
        $('#content .wrapper-spinner').animate({opacity: "show"},500);
        var id_form = $(e.currentTarget).attr('data-id');
        $.pjax.reload('#form-container', {push:false, type:'POST', data: {idForm:id_form}, url: '$url', skipOuterContainers: true, timeout: 5000});
    }
    // console.log(e);
  // alert('Вы нажали на элемент "foo"');
});


JS
,\yii\web\View::POS_END);


$this->registerCss(<<<'CSS'

.list-group-item {
    display: table;
    width: 100%;
}



#sidebar li .btn-group {
    display:none;
}
#sidebar li.selected .btn-group {
    display:block;
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

/*---*/
.wrapper-spinner {
    background-color: rgba(0, 0, 0, 0.5);
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

CSS
);