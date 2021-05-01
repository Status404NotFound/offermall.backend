// var tds_url = '//tds.advertfish.com';
var tds_url = '//t.crmka.net';
// var regorder_url = '//regorder.advertfish.com';
var regorder_url = '//r.crmka.net';

// var tds_url = '//tds.crm.my';
// var regorder_url = '//regorder.crm.my';

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function checkCookie(cookie_name) {
    var user = getCookie(cookie_name);
    if (user != "") {
        return true;
    } else {
        return false;
    }
}

function generateSID(len) {
    var charSet = 'abcdefghijklmnopqrstuvwxyz0123456789';
    var randomString = '';
    for (var i = 0; i < len; i++) {
        var randomPoz = Math.floor(Math.random() * charSet.length);
        randomString += charSet.substring(randomPoz, randomPoz + 1);
    }
    return randomString;
}

function generateViewHash(len) {
    var charSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var randomString = '';
    for (var i = 0; i < len; i++) {
        var randomPoz = Math.floor(Math.random() * charSet.length);
        randomString += charSet.substring(randomPoz, randomPoz + 1);
    }
    return randomString;
}

// function addView(isUnique)
// {
//     var form = $('form');
//     var offer_hash = form.find('input[name=offer_hash]').val();
//     $.ajax({
//         url : tds_url + '/view',
//         type : 'post',
//         // dataType: 'text',
//         data : {
//             referrer:location.href,
//             unique:isUnique,
//             offer_hash:offer_hash
//         },
//         complete : function() {
//             console.log('Sand');
//         }
//     });
// }

function doViewRequest(landing_id, isUnique) {
    var xhr = new XMLHttpRequest();
    var offer_hash = document.querySelector('[name="offer_hash"]').value;
    var params = 'referrer=' + location.href + '&unique=' + isUnique + '&offer_hash=' + offer_hash + '&landing_id=' + landing_id;
    xhr.open('POST', tds_url + '/view', false);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(params);
    return xhr;
}

function addView(landing_id, isUnique) {

    var view_request = doViewRequest(landing_id, isUnique);
    var i = 0;
    while ((view_request.status < 200 || view_request.status > 400) && i < 10) {
        view_request = doViewRequest(landing_id, isUnique);
        i+=1;
    }
}

function millisToMinutesAndSeconds(millis) {
    var minutes = Math.floor(millis / 60000);
    var seconds = ((millis % 60000) / 1000).toFixed(0);
    return minutes + ":" + (seconds < 10 ? '0' : '') + seconds;
}

function initForm(form_id) {
    var form_request = doFormRequest(form_id);
    var count = 0;
    while ((form_request.status < 200 || form_request.status > 400) && count < 10) {
        form_request = doFormRequest(form_id);
        count+=1;
    }

    var x = document.getElementsByClassName("form-wrapper");
    var i;
    for (i = 0; i < x.length; i++) {
        x[i].innerHTML += JSON.parse(form_request.responseText);
    }

    return true;
}

function doFormRequest(form_id) {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open("GET", tds_url + '/genform/editor/ajax-get-file-content?form_id=' + form_id, false); // false for synchronous request
    xmlHttp.send(null);
    return xmlHttp;
}

function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

// function dynamicallyLoadScript(url) {
//     var script = document.createElement("script"); //Make a script DOM node
//     script.src = url; //Set it's src to the provided URL
//     script.type = "text/javascript";
//     document.head.appendChild(script); //Add it to the end of the head section of the page (could change 'head' to 'body' to add it to the end of the body section instead)
// }

// dynamicallyLoadScript('//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js');
// dynamicallyLoadScript(tds_url + '/cookie.js');
// dynamicallyLoadScript(tds_url + '/session.js');
// dynamicallyLoadScript(tds_url + '/views.js');
// dynamicallyLoadScript('//cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.2.7/jquery.inputmask.bundle.min.js');


// function initLanding(landing_id) {
//     landing_id = landing_id;
// }

function initLanding(landing_id) {

    var geo = getParameterByName('geo');
    if (geo != null && geo != '') {
        var get_geo_url = tds_url + '/site/geo-info?landing_id=' + landing_id + '&geo_iso=' + geo;
    } else {
        var get_geo_url = tds_url + '/site/geo-info?landing_id=' + landing_id;
    }

    var geo_info = $.ajax({
        url: get_geo_url,
        // url : tds_url + '/site/geo-info?landing_id='+landing_id,
        type: 'get',
        async: false
    }).responseText;

    // console.log(geo_info);

    var geo_info_parsed = JSON.parse(geo_info);
    // console.log(geo_info_parsed);

    initForm(geo_info_parsed.form_id);


    $(".adfh-currency").append(geo_info_parsed.currency);
    $(".adfh-old-price").append(geo_info_parsed.old_price);
    $(".adfh-new-price").append(geo_info_parsed.new_price);
    $(".adfh-discount").append(geo_info_parsed.discount);
    $(".adfh-difference").append(geo_info_parsed.old_price - geo_info_parsed.new_price);

    console.log(geo_info_parsed.old_price - geo_info_parsed.new_price);

    var form = $('form');

    var language = document.documentElement.lang;
    if(language == "ar")
    {
        form.find("input[name=name]").attr('placeholder', 'إسم');
        form.find("input[name=name]").css('text-align', 'right');

        form.find("input[name=phone]").attr('placeholder', 'رقم الموبيل');
        form.find("input[name=phone]").css('text-align', 'right');

        form.find("textarea[name=address]").attr('placeholder', 'العنوان: مدينة، حي، منطقة، برج و غيرها');
        form.find("textarea[name=address]").css('text-align', 'right');
    }else{
        form.find("input[name=name]").attr('placeholder', 'Your name');
        form.find("input[name=phone]").attr('placeholder', 'Your mobile');
        form.find("textarea[name=address]").attr('placeholder', 'Your address exmpl. Town, area, tower name or Nr. and unit Nr.');
    }



    (function (d, e, id) {
        var div, body = d.getElementsByTagName('body')[0];
        if (d.getElementById(id)) return;
        div = d.createElement(e);
        div.id = id;
        body.appendChild(div);
    }(document, 'div', 'responseContainer'));


    var cookie_adfsh_view = checkCookie('adfsh_view_hash');
    var cookie_adfsh_session = checkCookie('adfsh_session_id');

    if (!cookie_adfsh_session) {
        setCookie('adfsh_session_id', generateSID(13), 0.01);
    }

    if (cookie_adfsh_view) {
        addView(landing_id, false);
    } else {
        setCookie('adfsh_view_hash', generateViewHash(24), 10000);
        addView(landing_id, true);
    }

    form.find('input[name=referrer]').val(location);
    form.find('input[name=sid]').val(getCookie('adfsh_session_id'));
    form.find('input[name=view_hash]').val(getCookie('adfsh_view_hash'));

    var fb_pixel = "<script>fbq('track','Lead');</script>";

    var resp = $(
        '<div class="success-msg-wrapper">' +
        '<div class="success-msg-container">' +
        '<div class="success-msg-content popup-content">' +
        '<div class="form-close"></div>' +
        '<div class="msg">' +
        '<p>Thank you for your order!</p>' +
        '<br>' +
        '<div id="summary">' +
        '<h6 style="text-align: center">Summary:</h6>' +
        '<table style="width: 100%; border: 1px solid black">' +
        '<tr>' +
        '<td class="sum-cell-caption"><p class="sum-field">Name:</p></td>' +
        '<td class="sum-cell-data"><p class="sum-field" id="sum-name"></p></td>' +
        '</tr>' +
        '<tr>' +
        '<td class="sum-cell-caption"><p class="sum-field">Phone:</p></td>' +
        '<td class="sum-cell-data"><p class="sum-field" id="sum-phone"></p></td>' +
        '</tr>' +
        '<tr>' +
        '<td class="sum-cell-caption"><p class="sum-field">Address</p></td>' +
        '<td class="sum-cell-data"><p class="sum-field" id="sum-address"></p></td>' +
        '</tr>' +
        '</table>' +
        '</div>' +
        '<br>' +
        '<p class="additional-form-text">To be aware of all new products, please leave your email</p>' +
        '<form id="email-back" class="email-back">' +
        '<input name="fields[email]" type="email" placeholder="email">' +
        '<input name="fields[cookie]" type="hidden" class="adfsh-ck">' +
        '<input name="fields[view_hash]" type="hidden" class="orderViewHash">' +
        '<input name="sid" type="hidden" class="sid">' +
        '<button type="submit">Submit</button>' +
        '</form>' +
        fb_pixel +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>' +
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
        '}' +
        ".error-input{border:1px solid red} .success-msg-wrapper{width:100%;height:100%;position:fixed;z-index:9999;opacity:0;top:0;left:0;background:rgba(0,0,0,.9);display:table}.success-msg-wrapper .success-msg-container{display:table-cell;vertical-align:middle}.success-msg-wrapper .success-msg-content{max-width:500px !important; width: 100% !important; margin:0 auto;background:#fff}.success-msg-wrapper .success-msg-content .msg{color:#000;padding:50px}.success-msg-wrapper .success-msg-content .msg p{font-size:20px;line-height:26px;text-align:center}.success-msg-wrapper.open--popup{-webkit-animation:fadeIn .7s forwards;animation:fadeIn .7s forwards}.success-msg-wrapper.open--popup .success-msg-content{-webkit-animation:moveIn .7s forwards;animation:moveIn .7s forwards}.success-msg-wrapper.close--popup{-webkit-animation:fadeOut .7s forwards;animation:fadeOut .7s forwards}.success-msg-wrapper.close--popup .success-msg-content{-webkit-animation:moveOut .7s forwards;animation:moveOut .7s forwards}@-webkit-keyframes fadeIn{from{opacity:0}to{opacity:1}}@keyframes fadeIn{from{opacity:0}to{opacity:1}}@-webkit-keyframes fadeOut{from{opacity:1}to{opacity:0}}@keyframes fadeOut{from{opacity:1}to{opacity:0}}@-webkit-keyframes moveIn{from{-webkit-transform:translateY(75%);transform:translateY(75%)}to{-webkit-transform:translateY(0);transform:translateY(0)}}@keyframes moveIn{from{-webkit-transform:translateY(75%);transform:translateY(75%)}to{-webkit-transform:translateY(0);transform:translateY(0)}}@-webkit-keyframes moveOut{from{-webkit-transform:translateY(0);transform:translateY(0)}to{-webkit-transform:translateY(-75%);transform:translateY(-75%)}}@keyframes moveOut{from{-webkit-transform:translateY(0);transform:translateY(0)}to{-webkit-transform:translateY(-75%);transform:translateY(-75%)}}.email-back input{height:auto;text-indent:0;padding:5px 15px}.email-back button{border:1px solid #D5D5D5;padding:10px;background:0 0;transition:.3s}.email-back button:hover{background:#D5D5D5}.form-close{width:30px;height:30px;position:absolute;right:0;margin:-15px;background:#fff;border-radius:50%;-webkit-transition:.4s;transition:.4s;cursor:pointer}.form-close:hover{background:#B2B2B2}.form-close:after,.form-close:before{display:block;position:absolute;content:'';width:50%;height:2px;background:#D8D8D9;top:49%;left:24%;-webkit-transform:rotate(-45deg);-ms-transform:rotate(-45deg);transform:rotate(-45deg)}.form-close:after{-webkit-transform:rotate(45deg);-ms-transform:rotate(45deg);transform:rotate(45deg)}p.additional-form-text{font-size:14px!important}.success-msg-content{width:100%;max-width:440px}" +
        "form{width:100%;border:none;text-align:left;position:relative;border-radius:15px}form input{width:100%;line-height:35px;height:45px;border:1px solid #999;margin:4px 0;font-size:15px;background-color:#fff;padding-left:30px;border-radius:3px}.email-back button{border:1px solid #999!important;width:100%!important;border-radius:3px;font-size:20px}.form-close{margin:0!important}.form-close::after,.form-close::before{background:#111!important}.success-msg-wrapper .success-msg-content .msg p{margin:5px 0!important}" +
        '</style>'
    );

    function isValid(inputs) {
        var inputsStatusFlag = true;

        inputs.each(function () {
            var input = $(this);

            if (input.attr('type') == 'tel' && !validPhone(input.val())) {
                setErrorClass(input);
                inputsStatusFlag = false;
            }
        });
        return inputsStatusFlag;
    }

    function setErrorClass(input) {

        var style = document.createElement("style");
        style.appendChild(document.createTextNode(".error-input{border:1px solid red !important;}"));
        document.head.appendChild(style);

        input.addClass('error-input');
        setTimeout(function () {
            input.removeClass('error-input');
        }, 4000);
    }

    function validPhone(phone) {
        phone = phone.replace(/\s/g, '');
        phone = phone.replace(/_/g, '');
        phone = phone.replace(/\+/g, '');
        console.log('phone: ' + phone);
        console.log(phone.length);
        return phone.length > 9;

    }

    function clearInputs(inputs) {
        var input;
        inputs.each(function () {
            input = $(this);

            if (input.attr('type') == 'hidden' || input.attr('type') == 'submit')
                return;

            input.val('');
        });
    }

    var start_date = new Date();
    var start = start_date.getTime();

    form.submit(function () {

        var end_date = new Date();
        var end = end_date.getTime();
        var ms = end - start;
        form.find('input[name=view_time]').val(millisToMinutesAndSeconds(ms));

        var formdata = $(this).serialize();

        var inputs = $(this).find('input, textarea');
        var names = [];
        inputs.each(function () {
            var input = $(this);
            console.log('input-' + input.attr('name') + ': ' + input.val() + '. Length: ' + input.val().length);
            if (input.hasClass('required')) {
                if (input.val().length < 1) {
                    var classes = '.' + input.attr('class').split(' ').join('.');
                    names.push(classes);
                }
            }
        });
        if (names.length > 0) {
            names.forEach(function (elem) {
                var input = $(form).find(elem);
                console.log(input);
                input.css('border', '2px solid red');
                input.css('background-color', '#fbc5c5');
            });
            console.log('names');
            return false;
        }
        if (!isValid(inputs)) {
            console.log('isValid!');
            return false;
        }

        $.ajax({
            url: 'conversions.php',
            type: 'post',
            dataType: 'text',
            data: formdata,
        });


        $.ajax({
            type: "POST",
            url: regorder_url + "/order/",
            data: formdata,
            complete: function () {
                // requestRunning = false;
                //console.log(requestRunning);
                // console.log(response);


                var response = resp.clone(),
                    close = response.find('.form-close'),
                    addForm = response.find('form'),
                    sid = form.find('[name=sid]').val();


                var sumName = form.find('input[name=name]').val(),
                    sumTel = form.find('input[name=phone]').val(),
                    sumAdr = form.find('textarea').val();

                var findCaption = function (elem, color) {
                    var caption = elem.closest('tr').find('.sum-cell-caption').find('p');
                    caption.css('color', color);
                };


                if (sumName == '') {
                    findCaption(response.find('#sum-name'), 'red');
                } else {
                    findCaption(response.find('#sum-name'), 'green');
                }
                if (sumTel == '') {
                    findCaption(response.find('#sum-phone'), 'red');
                } else {
                    findCaption(response.find('#sum-phone'), 'green');
                }
                if (sumAdr == '') {
                    findCaption(response.find('#sum-address'), 'red');
                } else {
                    findCaption(response.find('#sum-address'), 'green');
                }

                // console.log(sumName + ';' + sumTel + ';' + sumAdr);
                // console.log(form);

                response.find('#sum-name').html(sumName);
                response.find('#sum-phone').html(sumTel);
                response.find('#sum-address').html(sumAdr);

                response.addClass('open--popup');
                addForm.find('.orderViewHash').val(getCookie('adfsh_view_hash'));
                addForm.find('.sid').val(sid);
                // response.find('.adfsh-ck').val(adfshCK);
                $('#responseContainer').append(response);
                clearInputs(inputs);

                close.click(function () {
                    response.removeClass('open--popup').addClass('close--popup');
                    setTimeout(function () {
                        response.remove();
                    }, 700);
                });

                addForm.submit(function (event) {
                    event.preventDefault();
                    var inputs = $(this).find('input, textarea');

                    if (!isValid(inputs))
                        return false;
                    data = $(this).serialize();

                    $.ajax({
                        url: regorder_url + '/order/save-email',
                        type: 'post',
                        data: data,
                        complete: function () {
                            response.removeClass('open--popup').addClass('close--popup');
                            setTimeout(function () {
                                response.remove();
                            }, 700);
                        }
                    });

                });

            }
        });

        return false;
    });

    if (geo_info_parsed.phone_code != null) {
        form.find("input[name=phone]").inputmask({
            mask: "+\\" + parseInt(geo_info_parsed.phone_code, 10) + " 99999999[9][9][9][9]",
            greedy: false
        });  //static mask
    }
}
