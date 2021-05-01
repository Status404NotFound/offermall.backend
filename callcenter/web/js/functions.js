/*
    $('.element').click(function() {
        var operator_status = $('#record-status').val();
        setOperatorStatus(operator_status);
        console.log(operator_status);
    });

function setOperatorStatus(data) {

    $('#record-status').val(data.status);

    $.post({
       url: '/operator-conf/ajax-change-operator-status/',
           dataType: 'json',
           data: data,
           success: function(data) {
               if (data.status === 'success') {
               }else{
                    alert('Failed');
               }
       },
    });
}*/







