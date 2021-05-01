$('#pay_online_1').click(function () {
    // console.log(lmc_response); // From success.
    // console.log('HREF    ' + window.location.href);
    // console.log('landing_id ' + land_id);
    // '&amount=' + lmc_response.amount +

    console.log('pay_online_1');

    var params_string = '?order_id=' + lmc_response.order_hash +
        '&currency=' + lmc_response.currency +
        '&amount=129' +
        '&tid=' + new Date().getTime() +
        '&redirect_url=http://pricera.net/ccavenue_non_seamless_kit/ccavResponseHandler.php' +
        '&cancel_url=http://pricera.net/ccavenue_non_seamless_kit/ccavResponseHandler.php';

    window.open('http://pricera.net/ccavenue_non_seamless_kit/ccavRequestHandler.php' + params_string);
});

$('#pay_online_2').click(function () {


    console.log('pay_online_2');

    var params_string = '?order_id=' + lmc_response.order_hash +
        '&currency=' + lmc_response.currency +
        '&amount=199' +
        '&tid=' + new Date().getTime() +
        '&redirect_url=http://pricera.net/ccavenue_non_seamless_kit/ccavResponseHandler.php' +
        '&cancel_url=http://pricera.net/ccavenue_non_seamless_kit/ccavResponseHandler.php';

    window.open('http://pricera.net/ccavenue_non_seamless_kit/ccavRequestHandler.php' + params_string);
});

$('#cash_on_delivery').click(function () {
    document.form_name.submit();
});

/**
 <form method="post" name="redirect"
 action="http://fish.tds/site/pricera-avenue">';

 <?php foreach ($_REQUEST as $param) : ?>
 <input type=hidden name=encRequest value='<?= $param ?>'>;
 <?php endforeach; ?>
 </form>

 <script language='javascript'>document.redirect.submit();</script>*/

/**
 MID : 43560
 Access code : AVNJ02EC51AY38JNYA
 Encryption Key:  C9C529314471E7563C52B75C65A6B965

 Once you reach the payment page you may use the below test card to check
 the successful response of the transaction.
 Also the flow would not take to you 3D secure verification process since
 it’s the test card, once you go live the 3D secure verification would be done.

 Test card   5123456789012346,
 Expiry date   (05/21)
 Security code   123

 The post action URL must be
 https://secure.ccavenue.ae/transaction/transaction.do?command=initiateTransaction
 for both LIVE and TEST environement.

 */