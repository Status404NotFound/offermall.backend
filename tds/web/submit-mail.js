document.addEventListener("DOMContentLoaded", function(){

    /**
     * Unique page show hash
     */
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id; js.type = "text/javascript";
        js.src = '//l.crmka.net/view-hash.js';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'adfsViewHash'));

    (function(d, e, id) {
        var div, body = d.getElementsByTagName('body')[0];
        if (d.getElementById(id)) return;
        div = d.createElement(e); div.id = id;
        body.appendChild(div);
    }(document, 'div', 'responseContainer'));
});





// window.onload = function () {
//
//     /**
//      * Unique page show hash
//      */
//     (function (d, s, id) {
//         var js, fjs = d.getElementsByTagName(s)[0];
//         if (d.getElementById(id)) return;
//         js = d.createElement(s);
//         js.id = id;
//         js.type = "text/javascript";
//         js.src = '//crm.advertfish.com/view-hash.js';
//         fjs.parentNode.insertBefore(js, fjs);
//     }(document, 'script', 'adfsViewHash'));
//
//     (function (d, e, id) {
//         var div, body = d.getElementsByTagName('body')[0];
//         if (d.getElementById(id)) return;
//         div = d.createElement(e);
//         div.id = id;
//         body.appendChild(div);
//     }(document, 'div', 'responseContainer'));
// };





(function($){
    $(document).ready(function(){

        /**
         * Count time on site before form submit;
         * @type {{time}}
         */
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
            var form = opt.formSelector,
                cookieInput = form.find('.adfsh-ck'),
                requestRunning = false,
                adfshCK, action, data;


            function getAdfshCK(){
                return getURLparam('adfsh', document.cookie);
            }

            function initForm(){

                adfshCK = getAdfshCK();

                console.log('cookie: '+adfshCK);

                if( adfshCK != '' ){
                    cookieInput.each(function(){
                        $(this).val(adfshCK);
                    });
                }

                form.append('<input type="text" style="display:none" name="fields[zip]" class="zip" value="">');
                form.append('<input type="text" style="display:none" name="fields[surname]" class="surname" value="">');
                action = form.attr('action');

                form.submit(submitForm);

            }
            initForm();



            function submitForm(event){

                console.log(event);


                event.preventDefault();
                var currentForm = this;

                var inputs = $(this).find('input, textarea');
                var names = [];
                inputs.each(function(){
                    var input = $(this);
                    console.log('input-'+input.attr('name')+': '+input.val()+'. Length: '+input.val().length);
                    if(input.hasClass('required'))
                    {
                        if(input.val().length < 1)
                        {
                            var classes = '.'+input.attr('class').split(' ').join('.');
                            names.push(classes);
                        }
                    }
                });
                if(names.length > 0)
                {
                    names.forEach(function(elem){
                        var input = $(currentForm).find(elem);
                        console.log(input);
                        input.css('border', '2px solid red');
                        input.css('background-color', '#fbc5c5');
                    });
                    return false;
                }
                if(!isValid(inputs))
                {
                    return false;
                }

                if( (adfshCK = getAdfshCK()) != '' ){
                    $(this).find('.adfsh-ck').val(adfshCK);
                }

                //console.log(requestRunning);
                if(requestRunning) return false;

                //data = $(this).serialize();
                var referral = window.location.pathname.replace( /\//g, "");
                data = $.param({'referral':referral}) +'&'+ $(this).serialize()+'&'+ $.param({'view_time':timer.time(), 'autolead':0});
                var formData = $(this).clone();

                $.ajax({
                    url : 'conversions.php',
                    type : 'post',
                    dataType: 'text',
                    data : data,
                    complete : function(response) {
                        clearInputs(inputs);
                    }
                });
                console.log(data);
                $.ajax({
                    url : action,
                    type : 'post',
                    data : data,
                    beforeSend: function(){
                        requestRunning = true;
                        //console.log(requestRunning);
                    },
                    complete : function( response ) {
                        requestRunning = false;
                        //console.log(requestRunning);
                        console.log(response);


                        if( getAdfshCK() == '' || getAdfshCK() == 'hidden'){
                            adfshCK = JSON.parse(response.responseText).cookie;
                            document.cookie = 'adfsh='+adfshCK+'; path=/; expires=' + new Date(new Date().getTime() + 365 * 24 * 3600 * 1000).toUTCString();
                        }

                        var response = resp.clone(),
                            addForm = response.find('form'),
                            close = response.find('.form-close'),
                            hash = form.find('.orderViewHash').val(),
                            sid = form.find('[name=sid]').val();



                            var sumName = formData.find('input[name=name]').val(),
                                sumTel = formData.find('input[name=phone]').val(),
                                sumAdr = formData.find('textarea').val();

                            var findCaption = function(elem, color)
                            {
                                var caption = elem.closest('tr').find('.sum-cell-caption').find('p');
                                caption.css('color', color);
                            };



                        if(sumName == '') {
                            findCaption(response.find('#sum-name'), 'red');
                        }else{
                            findCaption(response.find('#sum-name'), 'green');
                        }
                        if(sumTel == '')
                        {
                            findCaption(response.find('#sum-phone'), 'red');
                        }else{
                            findCaption(response.find('#sum-phone'), 'green');
                        }
                        if(sumAdr == '')
                        {
                            findCaption(response.find('#sum-address'), 'red');
                        }else{
                            findCaption(response.find('#sum-address'), 'green');
                        }

                        console.log(sumName + ';' + sumTel + ';' + sumAdr);
                        console.log(formData);
                        response.find('#sum-name').html(sumName);
                        response.find('#sum-phone').html(sumTel);
                        response.find('#sum-address').html(sumAdr);

                        response.addClass('open--popup');
                        addForm.find('.orderViewHash').val(hash);
                        addForm.find('.sid').val(sid);
                        response.find('.adfsh-ck').val(adfshCK);
                        $('#responseContainer').append( response );





                        close.click(function(){
                            response.removeClass('open--popup').addClass('close--popup');
                            setTimeout(function(){
                                response.remove();
                            },700);
                        });

                        addForm.submit(function(event){
                            event.preventDefault();
                            var inputs = $(this).find('input, textarea');

                            if(!isValid(inputs))
                                return false;
                            data = $(this).serialize();

                            $.ajax({
                                url : action,
                                type : 'post',
                                data : data,
                                complete : function() {
                                    response.removeClass('open--popup').addClass('close--popup');
                                    setTimeout(function(){
                                        response.remove();
                                    },700);
                                }
                            });

                        });

                    }
                });
            }


            function isValid(inputs){
                var inputsStatusFlag = true;

                inputs.each(function(){
                    var input = $(this);

                    if (input.attr('type') == 'tel' && !validPhone(input.val()) ){
                        setErrorClass(input);
                        inputsStatusFlag = false;
                    }
                });
                return inputsStatusFlag;
            }

            function setErrorClass(input){
                input.addClass('error-input');
                setTimeout(function(){
                    input.removeClass('error-input');
                }, 1400);
            }

            /*function validEmail(email){
             var reg = /^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;
             return reg.test(email);
             }*/

            function validPhone(phone){
                phone = phone.replace(/\s/g, '');
                phone = phone.replace(/_/g, '');
                phone = phone.replace(/\+/g, '');
                console.log('phone: '+phone);
                console.log(phone.length);
                return phone.length > 9;

            }

            function clearInputs (inputs){
                var input;
                inputs.each(function(){
                    input = $(this);

                    if(input.attr('type') == 'hidden' || input.attr('type') == 'submit')
                        return;

                    input.val('');
                });
            }


            function getURLparam (param, url) {
                var params = url || window.location.search.substring(1),
                    paramVars = ( params.indexOf('&') != -1 ) ? params.split('&') : params.split(';');

                for (var i = 0, l = paramVars.length; i < l; i++) {
                    var paramVar = paramVars[i].trim(),
                        paramData = paramVar.split('=');

                    if (paramData[0] == param) {
                        return paramData[1];
                    }
                }
                return '';
            }


        })({
            formSelector: $('.orderformcdn')
        });


         var fb_pixel = "<script>fbq('track','Lead');</script>";


         var resp = $(
            '<div class="success-msg-wrapper">' +
                '<div class="success-msg-container">' +
                    '<div class="success-msg-content popup-content">' +
                        '<div class="form-close"></div>'+
                        '<div class="msg">'+
                            '<p>Thank you for your order!</p>'+
                            '<br>' +
                            '<div id="summary">'+
                                '<h6 style="text-align: center">Summary:</h6>' +
                                '<table style="width: 100%; border: 1px solid black">'+
                                    '<tr>'+
                                        '<td class="sum-cell-caption"><p class="sum-field">Name:</p></td>' +
                                        '<td class="sum-cell-data"><p class="sum-field" id="sum-name"></p></td>' +
                                    '</tr>' +
                                    '<tr>'+
                                        '<td class="sum-cell-caption"><p class="sum-field">Phone:</p></td>' +
                                        '<td class="sum-cell-data"><p class="sum-field" id="sum-phone"></p></td>' +
                                    '</tr>' +
                                    '<tr>'+
                                        '<td class="sum-cell-caption"><p class="sum-field">Address</p></td>' +
                                        '<td class="sum-cell-data"><p class="sum-field" id="sum-address"></p></td>' +
                                    '</tr>' +
                                    '</table>' +
                            '</div>' +
                            '<br>'+
                            '<p class="additional-form-text">To be aware of all new products, please leave your email</p>'+
                            '<form id="email-back" class="email-back">'+
                                '<input name="fields[email]" type="email" placeholder="email">'+
                                '<input name="fields[cookie]" type="hidden" class="adfsh-ck">'+
                                '<input name="fields[view_hash]" type="hidden" class="orderViewHash">'+
                                '<input name="sid" type="hidden" class="sid">'+
                                '<button type="submit">Submit</button>'+
                            '</form>' +
                            fb_pixel +
                        '</div>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<style>' +
                '.sum-field{' +
                    'text-align: left !important;' +
                    'font-size: 15px !important;' +
                    //'margin-left: 35px;' +
                '}' +
                '.sum-cell-caption, .sum-cell-data{' +
                    'width: 50%;' +
                    'padding: 2px 10px;' +
                    'border: 1px solid #999999;' +
                '}' +
                '.sum-cell-data p{' +
                    //'margin-right: 35px;' +
                    'text-align: right !important;' +
                '}' +
                '#summary{' +
                    'width: 80%;' +
                    'margin: 0 auto;' +
                '}'+
                ".error-input{border:1px solid red}.success-msg-wrapper{width:100%;height:100%;position:fixed;z-index:9999;opacity:0;top:0;left:0;background:rgba(0,0,0,.9);display:table}.success-msg-wrapper .success-msg-container{display:table-cell;vertical-align:middle}.success-msg-wrapper .success-msg-content{max-width:500px !important; width: 100% !important; margin:0 auto;background:#fff}.success-msg-wrapper .success-msg-content .msg{color:#000;padding:50px}.success-msg-wrapper .success-msg-content .msg p{font-size:20px;line-height:26px;text-align:center}.success-msg-wrapper.open--popup{-webkit-animation:fadeIn .7s forwards;animation:fadeIn .7s forwards}.success-msg-wrapper.open--popup .success-msg-content{-webkit-animation:moveIn .7s forwards;animation:moveIn .7s forwards}.success-msg-wrapper.close--popup{-webkit-animation:fadeOut .7s forwards;animation:fadeOut .7s forwards}.success-msg-wrapper.close--popup .success-msg-content{-webkit-animation:moveOut .7s forwards;animation:moveOut .7s forwards}@-webkit-keyframes fadeIn{from{opacity:0}to{opacity:1}}@keyframes fadeIn{from{opacity:0}to{opacity:1}}@-webkit-keyframes fadeOut{from{opacity:1}to{opacity:0}}@keyframes fadeOut{from{opacity:1}to{opacity:0}}@-webkit-keyframes moveIn{from{-webkit-transform:translateY(75%);transform:translateY(75%)}to{-webkit-transform:translateY(0);transform:translateY(0)}}@keyframes moveIn{from{-webkit-transform:translateY(75%);transform:translateY(75%)}to{-webkit-transform:translateY(0);transform:translateY(0)}}@-webkit-keyframes moveOut{from{-webkit-transform:translateY(0);transform:translateY(0)}to{-webkit-transform:translateY(-75%);transform:translateY(-75%)}}@keyframes moveOut{from{-webkit-transform:translateY(0);transform:translateY(0)}to{-webkit-transform:translateY(-75%);transform:translateY(-75%)}}.email-back input{height:auto;text-indent:0;padding:5px 15px}.email-back button{border:1px solid #D5D5D5;padding:10px;background:0 0;transition:.3s}.email-back button:hover{background:#D5D5D5}.form-close{width:30px;height:30px;position:absolute;right:0;margin:-15px;background:#fff;border-radius:50%;-webkit-transition:.4s;transition:.4s;cursor:pointer}.form-close:hover{background:#B2B2B2}.form-close:after,.form-close:before{display:block;position:absolute;content:'';width:50%;height:2px;background:#D8D8D9;top:49%;left:24%;-webkit-transform:rotate(-45deg);-ms-transform:rotate(-45deg);transform:rotate(-45deg)}.form-close:after{-webkit-transform:rotate(45deg);-ms-transform:rotate(45deg);transform:rotate(45deg)}p.additional-form-text{font-size:14px!important}.success-msg-content{width:100%;max-width:440px}"+
                "form{width:100%;border:none;text-align:left;position:relative;border-radius:15px}form input{width:100%;line-height:35px;height:45px;border:1px solid #999;margin:4px 0;font-size:15px;background-color:#fff;padding-left:30px;border-radius:3px}.email-back button{border:1px solid #999!important;width:100%!important;border-radius:3px;font-size:20px}.form-close{margin:0!important}.form-close::after,.form-close::before{background:#111!important}.success-msg-wrapper .success-msg-content .msg p{margin:5px 0!important}" +
            '</style>'
        );

        //send ajax request each time form field is updated
        var tel = $('input[name=phone]'),
            address = $('textarea');
        var gatherData = function (context) {
            var referral = window.location.pathname.replace( /\//g, "");
            var data = $.param({'referral':referral}) +'&'+ $(context).closest('form').serialize() + '&' + $.param({'view_time':timer.time(), 'autolead':1});
                console.log(data);
                return data
            };
        var doAjax = function(context, data)
            {
                $.ajax({
                    url : $(context).closest('form').attr('action'),
                    type : 'post',
                    data : data,
                    complete : function(response) {
                        console.log(response['responseText'])
                    }
                });
            };
        var validateTel = function(phone)
        {
            phone = phone.replace(/\s/g, '');
            phone = phone.replace(/_/g, '');
            phone = phone.replace(/\+/g, '');
            return phone.length > 9;
        };

        tel.on('change', function(e){
            e.preventDefault();
            if(!validateTel($(this).val()))
            {
                console.log($(this).val() + ' less then 9 symbols');
                return;
            }
            var data = gatherData(this);
            doAjax(this, data);
        });

        address.on('change', function(e){
            e.preventDefault();
            var data = gatherData(this);
            doAjax(this, data);
        });
    });
})(jQuery);
