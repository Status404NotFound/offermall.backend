function generateViewHash(len) {
    var charSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var randomString = '';
    for (var i = 0; i < len; i++) {
        var randomPoz = Math.floor(Math.random() * charSet.length);
        randomString += charSet.substring(randomPoz,randomPoz+1);
    }
    return randomString;
}

function addView(isUnique)
{
    var form = $('form');
    var offer_hash = form.find('input[name=offer_hash]').val();
    $.ajax({
        // url : '//tds.advertfish.com/view',
        url : '//tds.af/view',
        type : 'post',
        // dataType: 'text',
        data : {
            referrer:location.href,
            unique:isUnique,
            offer_hash:offer_hash
        },
        complete : function() {
            console.log('Sand');
        }
    });
}