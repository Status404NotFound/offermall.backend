<?php
/** @var string $modelForm */
/** @var Fields $modelFields */
use \tds\modules\genform\models\fields\Fields;

?>
    <div id="wrapper" class="">
        <div id="sidebar" class="col-xs-4">
            <?= $this->render('_sidebar-fields', [
                'modelFields' => $modelFields,
            ]) ?>
        </div>
        <div id="content" class="col-xs-8">
            <div id="content-header"></div>
            <div id="content-form" class="">
                <div class="col-xs-8">

                    <?= $modelForm ?>

                </div>
            </div>
        </div>
    </div>
<?php
$this->registerJS(<<<JS

    $('#left-panel .panel-heading').click(function(){
        var href = $(this).find('a.collapse-toggle').attr('href');
        $('.collapse .in').collapse('hide');
        $(href).collapse('toggle');
        // console.log(href);
    });

/* *** *** */
var flagPopover = false;
var flagRemove = false;
var flagCreate = false;

    //Сортування  і збереженя порядку
    jQuery(".genform .form-content").sortable({
        revert: true,
        stop:  function (e,ui){
            //Стереть временые стили
            ui.item[0].attributes.style.value = '';
            console.log('sortable', 'stop');
        },
        over: function (e,ui){
            //Показать место для блока
            $(ui.placeholder).show(300);
            console.log('sortable','over', '--- showField');
        },
        out: function (e,ui){
            //Скрыть место для блока
            $(ui.placeholder).hide(300);
            console.log('sortable','out', '--- hideField');
        },
        start: function(){
            //Разрешить показовать корзину
            flagRemove = true;
            flagPopover = false;
            //Визвать событие показа корзины
            enableTrash();
            hidePopover();
            console.log('sortable','start', 'remove = true', 'popover = false', 'enableTrash()', 'hidePopover()');
        }
    }).disableSelection();

    //Видалення поля
    $('#sidebar .remove-fields').droppable({
        accept: 'div',
        drop: function (event, ui) {
            ui.helper.remove();
            $('#sidebar .remove-fields').css('display', 'none');
            console.log('droppable','drop', '--- removeField;', '--- hideTrash;');
        }
    });
    
    //Створення нових полів
    $("#left-panel .ui-draggable").draggable({
        connectToSortable: '.genform .form-content',
        // helper: 'clone',
        revert: 'invalid',
        scroll: false,
        helper: function (event) {
            var elem = event.currentTarget.cloneNode(true);
            flagCreate = true;
            
            hidePopover();
            onClickMouse(elem);
            console.log('draggable','helper', 'create = true', 'onClickMouse');
            
            return elem;
        },
        stop: function(event, ui) {
            flagCreate = false;
            flagRemove = false;
            console.log('draggable','stop', 'remove = false', 'create = false');
        }
    });
    
    onClickMouse(".genform  [data-toggle='popover']");
//*/
/* *** start script *** */
/* *** function *** */
//Событие мыши (mousedown|mouseup) по указаном элементу, при котором срабатывает показ/скрытие корзины
function onClickMouse(selector) {
    $(selector).on('mousedown',function(event){
        flagPopover = true;
        enableTrash();
        console.log('mousedown', 'popover = true', 'enableTrash()');
    }).on('mouseup',function(){
        $('#sidebar .remove-fields').css('display', 'none');
        //Показати вибраний
        triggerPopover(this);
        console.log('mouseup', '--- hideTrash', 'triggerPopover(this)');
    });
}

function enableTrash() {
    if (flagRemove && !flagCreate) {
        flagRemove = false;
        $('#sidebar .remove-fields').css('display', 'block');
        console.log('enableTrash', 'remove = false', '', '--- showTrash');
    }
}

function triggerPopover(element) {
    if (!flagPopover) {
        return false;
    }
    flagPopover = false;
    var oldPopover = $(".genform .popover");
    
    if (oldPopover.length > 0 && oldPopover[0] === element.nextElementSibling) {
        //Если клик был по одному и тому же полю (поле которое уже открыто) - скрыть его.
        oldPopover.popover("hide");
            console.log('triggerPopover', '--- hidePopover');
    } else {
        //Если клик был не повторным то тогда:
        //-- Скрываем все остальные
        if (oldPopover.length) {
            oldPopover.popover("hide");
            console.log('triggerPopover', '--- hidePopover');
        }
        //-- Показываем попандер через 0,1 секунду
        setTimeout(function() {
            $(element).popover("show");
            console.log('triggerPopover', '--- showPopover');
        }, 100);
    }
    
    return true;
}

function hidePopover() {
    //Заховати поповер
    $(".popover").popover("hide");
    console.log('--- hidePopover');
}

JS
,\yii\web\View::POS_END);


$this->registerCss('
.list-group-item {
    display: table;
    width: 100%;
}
');