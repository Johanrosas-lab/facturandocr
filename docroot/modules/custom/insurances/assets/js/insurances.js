/**
 * @file
 * Provides JavaScript for Insurance Field.
 */

(function ($) {

Drupal.behaviors.insurances = {
  attach: function (context) {
    $("textarea[id^='edit-field-order-fields']")
      .parent()
      .parent()
      .parent()
      .css('display','none');
    $( "#sortable1, #sortable2" ).sortable({
      connectWith: ".connectedSortable",
      receive: function( event, ui ) {
        var order_fields = new Array();
        var entities = {};
        $('#sortable2').find('li').each(function(){
          var entity = $(this).attr('data-entity');

          if(!entities[entity]){
            var type = 'single'
            if(entity ==  'beneficiaries') type = 'list';
            entities[entity] = {
              'entity': entity,
              'bundle': $(this).attr('data-bundle'),
              'type': type,
              'fields': new Array()
            };
          }
          //Create field
          var field = {
            'machine_name' : $(this).attr('data-machine-name'),
            'label'        : $(this).html(),
            'type'         : $(this).attr('data-type'),
          }
          field.settings = $(this).attr('data-settings') != '[]' ?
            JSON.parse($(this).attr('data-settings')) : null;

          //Save Field
          entities[entity].fields.push(field);
        });
        Object.keys(entities).forEach(function(key) {
            order_fields.push(entities[key]);
        });
        console.log(JSON.stringify(order_fields));
        $("textarea[id^='edit-field-order-fields']").val(
          JSON.stringify(order_fields)
        );
      }
    }).disableSelection();
  },
  detach: function (context) {

  }
};

})(jQuery);
