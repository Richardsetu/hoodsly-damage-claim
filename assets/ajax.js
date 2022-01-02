;(function ($) {

    function hoodsly_damage_claim( order_id ) {

        $.ajax({
            type: 'POST',
            url : damageClaim.ajaxUrl,
            data: {
                action: "order_item_details",
                _nonce: damageClaim.damage_nonce, 
                order_id : order_id
            },
            beforeSend: function () {
               $(".select2-search__field").attr('placeholder', 'Loading...');
            },
            success: function(response){

               var data = JSON.parse(response);
               $("#input_1_8").html(""); 
               data.forEach(function(items) {
                var rp = items.replace('<p>','');
                var item = rp.replace('</p>','');
                $("#input_1_8").append(`<option value='${item}' data-badge=''>${item}</option>`);
               })    
            },
            complete: function(){
               
            },
            error: function(data) {
               
            }

        })
        
    }

    $("#input_1_7").on("change", function (e) {
        var order_id = $(this).val();
        hoodsly_damage_claim( order_id );
    });

})(jQuery)