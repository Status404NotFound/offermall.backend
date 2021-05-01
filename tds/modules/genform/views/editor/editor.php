<?php
/** @var string $modelForm */
use yii\widgets\Pjax;

/** @var \tds\modules\genform\models\fields\ThemeFields $modelTheme */

?>
    <div id="wrapper" class="">
        <div id="sidebar" class="col-xs-4">
            <?= $this->render('_sidebar-editor', [
                'modelTheme' => $modelTheme,
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
            <?= $this->render('_content-editor-form', [
                'listPagesForm' => [],
                'form' => $modelForm,
            ]) ?>
            <?php
                $this->registerJS(<<<JS
    // Прикрепить событие сортировки/перетягивания блоков формы
    sort();
    eventForItems();
    // Прикрепить событие клика на редактируемые блоки формы
    onClickMouse(".genform  [data-toggle='popover']");
JS
)
            ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
<?php
/** @var string $url */
$this->registerJS(<<<JS


/* ***  *** */
var isCreate = false;
var isDelete = false;
var isUpdate = false;

var isActiveSort = false;
var isActiveCreate = false;
var isActivePopover = false;

/* *** Обработчик события которое скрывает/показывает элементы для перетаскивания. *** */ 
$('#left-panel .panel-heading').click(function(){
        //В атрыбуте href хранится идентификатор блока с элементами
        var href = $(this).find('a.collapse-toggle').attr('href');
        //Скрыть все вкладки
        $('.collapse .in').collapse('hide');
        //Показать только выбраную
        $(href).collapse('toggle');
    });


/* *** Показать корзину *** */
function showTrush() {
    $('#sidebar .remove-fields').css('display', 'block');
}
/* *** Скрыть корзину *** */
function hideTrush() {
    $('#sidebar .remove-fields').css('display', 'none');
}

/* *** Показать корзину *** */
function showPreloader() {
    $('#content .wrapper-spinner').animate({opacity: "show"},500);
}
/* *** Скрыть корзину *** */
function hidePreloader() {
    $('#content .wrapper-spinner').animate({opacity: "hide"},500);
}

/* *** Показать поповер *** */
var oldPopover = null;
function showPopover(element) {
    if (isActivePopover) {
        if (oldPopover !== element) {
            hidePopover();
            $(element).popover("show");
        }
    } else {
        $(element).popover("show");
    }
    isActivePopover= true;
    oldPopover = element;
    // console.log('showPopover', element)
}
/* *** Скрыть поповер *** */
function hidePopover() {
    isActivePopover = false;
    $(".popover").popover("hide");
    // console.log('hidePopover')
}

/* *** Удалить элемент *** */
function deleteElem(arrIdFields, element) {
    showPreloader();
    
    $.pjax.reload('#form-container', {
        push:false,
        type:'POST',
        data:
        {
            idForm:{$idForm},
            data:JSON.stringify(arrIdFields),
            event:'delete',
            element:JSON.stringify({
                id: element.dataset.field_id,
                type: element.dataset.field_type
            })
        },
        skipOuterContainers: true,
        timeout: 5000
    });
    //*/
}
/* *** Создать элемент *** */
function createElem(arrIdFields, element) {
    showPreloader();
    
    $.pjax.reload('#form-container', {
        push:false,
        type:'POST',
        data:
        {
            idForm:{$idForm},
            data:JSON.stringify(arrIdFields),
            event:'create',
            element:JSON.stringify({
                id: element.dataset.field_id,
                type: element.dataset.field_type
            })
        },
        skipOuterContainers: true,
        timeout: 5000
    });
}
/* *** Обновить порядок элементов *** */
function updateElem(arrIdFields, element) {
    showPreloader();
    
    $.pjax.reload('#form-container', {
        push:false,
        type:'POST',
        data:
        {
            idForm:{$idForm},
            data:JSON.stringify(arrIdFields),
            event:'update',
            element:JSON.stringify({
                id: element.dataset.field_id,
                type: element.dataset.field_type
            })
        },
        skipOuterContainers: true,
        timeout: 5000
    });
}
/* *** *** */
function saveProperty() {
    var popover = $('.popover-form').toArray();
    var data = {items:[]};
    var isUniqueValItem = true;
    
    
    if (popover.length) {
        var formData = $(popover[0]).serializeArray();
        
        $(formData).each(function(index, elem) {
            if ( elem.name != '_csrf' ) {
                data[elem.name] = elem.value;
            }
        });
        data.values = {};
        $('.popover .fields-items > .items').each(function(index, elem) {
            var value = $(elem).find('[data-type="a"]').val();
            var text = $(elem).find('[data-type="b"]').val();
           
                //TODO Костиль на проверку уникальности значений - начало!!!
            $(elem).find('[data-type="a"]').css('border','none');
            if (data.items[value] !== undefined || value === '' || ~value.indexOf('-?') ) {
                isUniqueValItem = false;
                if ( !~value.indexOf('-?') ) {
                    $(elem).find('[data-type="a"]').val(value + '-?');
                }
                $(elem).find('[data-type="a"]').css('border','1px solid red');
            }
            //TODO Костиль конец!
            
            data.values[value] = text;
        });
    }
    
    if ( isUniqueValItem ) {
        hidePopover();
        showPreloader();
        $.pjax.reload('#form-container', {
            push:false,
            type:'POST',
            data:
            {
                idForm:{$idForm},
                data:data,
                event:'property'
            },
            skipOuterContainers: true,
            timeout: 5000
        });
    }
    //*/
    
}

function eventForItems() {
    /* *** Событие для поповера: создание дополнительных полей *** */
    $(".genform").delegate("#addValues", "click", function () {
        //Отримати останій елемент
        var lastEem = $(".popover-content .fields-items .items").last();
        var clone, value, nameA, nameB;
        //console.log(lastEem);
        //Якщо елемент існує
        if (lastEem) {
            //Клонируем последний элемент
            clone = $(lastEem).clone();
            //Вставляет после него клон
            $(lastEem).after(clone);
            
            /* *** Делаем уникальным значение *** */
            //Получить текущее значение.
            value = $(clone).find('[data-type="a"]').val();
            //Если значение число то увеличим его на единицу, в противном случае ставим ноль.
            if (Number.isInteger(value * 1)) {
                value++;
            } else {
                value = 0;
            }
            //Присваеваем новое значение
            $(clone).find('[data-type="a"]').val(value);
        }
    });
    /* *** Событие для поповера: удаление дополнительных полей *** */
    $(".genform").delegate(".deleteValue", "click", function () {
    var elem = $(this).parents('.items');
    
    if ($(".deleteValue").length > 1) {
        $(elem).remove();
    }
});
}


/* *** Событие клик мыши *** */
function onClickMouse(selector) {
    $(selector).on('mouseup',function(){
        if (!isActiveSort) {
            showPopover(this);
        }
        // console.log('mouseup');
    });
}
/* *** Отслеживать все клики в body *** */
$('body').on('mousedown',function(e){
    
        // Если: включен поповер и  ненайдено ни одиного родительского элемента с класом '.genform'
        // (иначе "понятней" говоря, если клик произошел не по поповеру и вне формы => срываем поповер)
        if (isActivePopover && !$(e.toElement).parents('.genform').length) {
            hidePopover(this);
        }
        // console.log('mousedown');
    });

/* *** *** */

// Сортировка
function sort() {
    jQuery(".genform .form-content").sortable({
        connectWith: '#sidebar .remove-fields',
        revert: 500,
        scroll: false,
         tolerance: 'pointer',
        // Определяет функцию, код которой будет выполнен,
        // когда начинается перемещение сортируемого элемента.
        start: function() {
            isActiveSort = true;
            if (!isActiveCreate) {
                showTrush();
            }
            if (isActivePopover) {
                hidePopover();
            }
            // console.log('sortable','start');
        },
        // Определяет функцию, код которой будет выполнен,
        // когда сортировка завершиться.
        stop:  function (event,ui) {
            var arrIdFields = $('.genform .form-content .form-group').map(function(index, elem) {return {
                        'id':$(elem).data('field_id'),
                        'type':$(elem).data('field_type')
                    }}).toArray();
            isActiveSort = false;
            
            hideTrush();
            // Стереть временые стили, для блока который перетягивали
            // после того как он будет добавлен к форме.
            ui.item[0].attributes.style.value = '';
            if (isCreate) {
                isCreate = false;
                createElem(arrIdFields, event.toElement.parentElement);
                // console.log('sortable', 'receive', 'Add Data Field');
            } else if (isDelete) {
                isDelete = false;
                deleteElem(arrIdFields, event.toElement.parentElement);
                // console.log('sortable', 'remove', 'Remove Data field');
            } else if (isUpdate) {
                isUpdate = false;
                updateElem(arrIdFields, event.toElement.parentElement);
                // console.log('sortable', 'update', 'Update Data position');
            } else {
                // console.log('Unknown status');
            }
            
            // console.log('sortable','end');
        },
        // Определяет функцию, код которой будет выполнен,
        // когда один из связанный списков сортируемых элементов получит элемент из другого.
        receive:function(event) {
            isCreate = true;
            // Прицепить событие клика по элементу
            onClickMouse(event.toElement.parentElement);
        },
        // Определяет функцию, код которой будет выполнен,
        // когда сортируемый элемент был вытащен из списка и помещён другой список.  
        remove:function() {
            isDelete = true;
        },
        // Определяет функцию, код которой будет выполнен,
        // когда сортировка завершиться при условии,
        // что положение элементов в группе изменится.
        update:function(e,ui) {
            isUpdate = true;
        }
    });
}
    
    // Создание списка для удаления
    $('#sidebar .remove-fields').sortable({
        // Определяет функцию, код которой будет выполнен,
        // когда один из связанный списков сортируемых элементов получит элемент из другого.
        receive:function(elem, ui) {
            //Удалить только что получений элемент
            ui.item.remove();
        }
    });
    
    // Создание новых полей
    $("#left-panel .ui-draggable").draggable({
        connectToSortable: '.genform .form-content',
        helper: 'clone',
        revert: 'invalid',
        scroll: false,
         appendTo: 'body',
        // Определяет функцию, код которой будет выполнен,
        // когда начинается перемещение сортируемого элемента.
        start: function(event, ui) {
            // Фича)) При копировании делать размер перетаскиваемого 
            // блока таким же как у оригинала!
            // а то та зараза сжимается))
            $(ui.helper).css('width', $(event.toElement).css('width'));
            
            isActiveCreate = true;
            // console.log('draggable','start');
        },
        // Определяет функцию, код которой будет выполнен,
        // когда сортировка завершиться.
        stop: function() {
            isActiveCreate = false;
            // Фича)) Если элемент поводить над формой сортировка станет активной
            // но событие стоп у нее не срабатывает! Так что для перестраховки)
            
            isActiveSort = false;
            // console.log('draggable','stop');
        }
    });
    

JS
,\yii\web\View::POS_END);


$this->registerCss('
.list-group-item {
    display: table;
    width: 100%;
}
#content-header {
    margin-bottom: 50px;
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
');
