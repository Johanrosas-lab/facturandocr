(function($, Drupal) {
    "use strict";

    $(document).ready(function() {
        $("#edit-options .form-item-options label").before("<span class='klap_payment-remove' data-toggle='tooltip' data-placement='top' title='Eliminar tarjetas'><i class='fas fa-trash-alt'></i></span>");
        $(".panel-heading").after("<p class='success-message hidden'>La tarjeta favorito se ha actualizado con éxito!</p>");
        $("body").on('click', '.klap_payment-remove', function() {
            $(".remove-card-question").remove();
            $(this).before("<div id='loadingmessage' style='display:none'><img src = 'https://i.kinja-img.com/gawker-media/image/upload/s--LytxZcab--/c_fit,fl_progressive,q_80,w_636/1481054780733836946.gif'/></div ><div class='remove-card-question'><p class='text-danger'>Esta seguro que desea borrar esta tarjeta?</p><button type='button' class='btn btn-danger delete-card'>Borrar</button><button type='button' class='btn btn-light cancel-delete-card'>Cancelar</button></div>");

        });
        $("body").on('click', '.delete-card', function() {
            var cardMask = $(".remove-card-question").siblings('label').find(".klap_payment-number").text();
            $('.remove-card-question').hide();
            $('#loadingmessage').show();
            $.post("/remove-card/custom", { cardMask: cardMask })
                .done(function() {
                    $('.remove-card-question').parent().remove();
                    $('#loadingmessage').remove();
                    alert("La tarjeta fue eliminada con exito!");
                });
        });
        $("body").on('click', '.cancel-delete-card', function() {
            $(".remove-card-question").remove();
        });
        $("body").on('click', '#edit-options .option', function() {
            var cardMask = $(this).find(".klap_payment-number").text();
            $.post("/add-card/custom", { cardMask: cardMask });
            $(".success-message").removeClass('hidden');
            setTimeout(function(){
                $(".success-message").addClass('hidden');
            }, 2000);
        });
    });
})(jQuery, Drupal)
