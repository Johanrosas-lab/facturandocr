(function ($, Drupal) {
  "use strict";

  $(document).ready(function () {
    $("body").on('keyup keypress paste', 'input', function () {
      var empty = true
      $(":input").each(function () {
        if ($(this).val() === "" && !$( this ).is( "button" )) {
          empty = true;
          return false;
        }
          empty = false;
      });
      if (empty == false) {
        $('.js-form-submit').removeClass('disabled');
      }
      if (empty == true && !$('.js-form-submit').hasClass('disabled')) {
        $('.js-form-submit').addClass('disabled');
      }
    });
  });
})(jQuery, Drupal)
