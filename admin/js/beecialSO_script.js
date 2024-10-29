jQuery(document).ready(function($) {

    // Función testear conexión API
    $("#test-connection").click(function(){
        $('#test-response').html('');
        jQuery.ajax({
            type: "post",
            url: beecialSO_vars.ajaxurl,
            data: {
                'action': 'check_connection'
            },
            success: function(response){
                response = JSON.parse(response);
                if(response.status == 'Success'){
                    $('#test-response').append('<div class="test-connection" style="background-color:#70c774;color:#fff;padding:5px;border-radius:5px;display:inline-block;">'+response.message+'</div>');
                }else if(response.status == 'Error'){
                    $('#test-response').append('<div class="test-connection" style="background-color:#e14949;color:#fff;padding:5px;border-radius:5px;display:inline-block;">'+response.message+'</div>');
                }
            }
        });
    });

    // Función syncronizar pedidos
    $("#force-sync").click(function(){
        $('#sync-response').html('');
        jQuery.ajax({
            type: "post",
            url: beecialSO_vars.ajaxurl,
            data: {
                'action': 'sync_orders'
            },
            success: function(response){
                response = JSON.parse(response);
                if(response.status == 'Success'){
                    $('#sync-response').append('<div class="test-connection" style="background-color:#70c774;color:#fff;padding:5px;border-radius:5px;display:inline-block;">'+response.message+'</div>');
                }else if(response.status == 'Error'){
                    $('#sync-response').append('<div class="test-connection" style="background-color:#e14949;color:#fff;padding:5px;border-radius:5px;display:inline-block;">'+response.message+'</div>');
                }
            }
        });
    });
});