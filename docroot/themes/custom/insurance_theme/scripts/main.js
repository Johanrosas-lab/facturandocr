(function($, Drupal) {

    // Click for hide or show menu sidebar
    $('#canvasoff').click(function() {
        $('#row-offcanvas').toggleClass('active');
    });
    $('input[type=text]#edit-search').attr('placeholder', 'Buscar seguro');
    $('input[type=text]#edit-combine').attr('placeholder', 'Filtrar');

    $('input[name=search_api_fulltext].form-text').attr('placeholder', 'Buscar seguro');

    //footer div wraps
    $('#block-socialmedialinks,#block-shareeverywhereblock-2').wrapAll('<div class="social-media-buttons"></div>');
    $('#block-osclogo,#block-poweredby').wrapAll('<div class="footer-copyright-logo"></div>');
    //
    //add-card-form input $classes
    $("#klap-payment-add-card-form .form-type-number").removeClass("form-inline");

    //edit profile modal social media wrap
    $('.field--name-field-facebook, field--name-field-twitter, .field--name-field-instagram').wrapAll('<div class="socialmedia-form">');
    //

    //add info icon on email field on register form
    $('.register-form-first .form-type-email').prepend("<i class='fa fa-info-circle'></i>");

    $('#edit-field-user-product--wrapper legend span').append("<div class='close-popup-custom'><h2>X</h2></div>");
    $("#edit-field-user-product--wrapper").on('click', '.close-popup-custom', function() {
        $('#popup-field-group-pop-up-productos').modal('toggle')
    });
    $('#edit-field-user-product--wrapper .fieldset-wrapper').append("<div class='close-popup-custom'><p class='close-popup-custom-accept'>Aceptar</p></div>");

    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
    if ($('.product-content .alert-success a').length) {
        // Get the modal
        var modal = document.getElementById('myModal');
        // When the user clicks on the button, open the modal
        modal.style.display = "block";
        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    }

    $("body").on('click', '.close-modal-custom', function() {
        var modal = document.getElementById('myModal');
        modal.style.display = "none";
    });

    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
        $("body").on('submit', '.profile-form', function(e) {
            const phoneValue = $('.form-tel').val();
            if (phoneValue.length !== 8) {
                $('.form-tel').focus();
                $('.error-msg').remove();
                $('.form-tel').after("<p class='error-msg'>Debe poner un número valido de 8 dígitos.</p>");
                e.preventDefault();
            }
        });
        if ($('.slideshow-custom').length > 0) {
            $('.slideshow-custom').slick({
                autoplay: true,
                autoplaySpeed: 5000,
            });
        }
    });



})(jQuery, Drupal);
