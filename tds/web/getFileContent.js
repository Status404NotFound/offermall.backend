var tds_url = '//tds.af';
// var tds_url = '//fish.tds';

function getContent(form_id)
{
    var form_request = doFormRequest(form_id);

    while (form_request.status < 200 && form_request.status > 400)
    {
        form_request = doFormRequest(form_id);
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
    xmlHttp.open( "GET", tds_url + '/genform/editor/ajax-get-file-content?form_id=' + form_id, false ); // false for synchronous request
    xmlHttp.send( null );
    return xmlHttp;
}


// function getContent(form_id){
//     var req = new XMLHttpRequest();
//
//     if('withCredentials' in req) {
//         req.open('GET', tds_url + '/genform/editor/ajax-get-file-content?form_id=' + form_id, true);
//         req.onreadystatechange = function() {
//             if (this.readyState === 4) {
//                 if (this.status >= 200 && this.status < 400) {
//                     // document.getElementsByClassName('form-wrapper').innerHTML += JSON.parse(this.responseText);
//                     var x = document.getElementsByClassName("form-wrapper");
//                     var i;
//                     for (i = 0; i < x.length; i++) {
//                         x[i].innerHTML += JSON.parse(this.responseText);
//                     }
//                 } else {
//                     alert("Please, reload the page");
//                 }
//             }
//         };
//         req.send(null);
//     }
// }


function addCss(fileName) {

    var head = document.head,
        link = document.createElement('link');
    link.type = 'text/css';
    link.rel = 'stylesheet';
    link.href = fileName;

    head.appendChild(link);

    return null;
}

function getScript() {
    document.write('<' + 'script src="' + tds_url + '/form-handler.js' + '"' +
        ' type="text/javascript"><' + '/script>');


    return true;
}

