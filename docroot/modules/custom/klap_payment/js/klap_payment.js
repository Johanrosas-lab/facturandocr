(function($, Drupal) {
    /**
     * Add new command for reading a message.
     */
    Drupal.AjaxCommands.prototype.readMessage = function(ajax, response, status) {
        var idProduct = response.id;
        $("#product_form_" + idProduct + " .fa-times").addClass("hidden");
        $("#product_form_" + idProduct + " .fa-check").removeClass("hidden");
    }
})(jQuery, Drupal);