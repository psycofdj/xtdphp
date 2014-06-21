(function($) {
  // extends jquery validation plugin
  // http://jqueryvalidation.org/
  $.fn.wappform = function(options) {

    var settings = $.extend({
     errorClass : "has-error",
     validClass : "has-success",
     success : function(p_succes, p_el) {
       $(p_el)
         .parents("div.form-group")
         .tooltip("destroy");
       $(p_el)
         .parents("div.form-group")
         .removeClass("has-error")
         .find("span.form-control-feedback")
         .removeClass("glyphicon-remove")
         .addClass("glyphicon-ok");
     },
     errorPlacement : function(p_error, p_el) {
       $(p_el)
         .parents("div.form-group")
         .addClass("has-error")
         .find("span.form-control-feedback")
         .removeClass("glyphicon-ok")
         .addClass("glyphicon-remove");

       $(p_el)
         .parents("div.form-group")
         .tooltip("destroy")
         .tooltip({ title     : $(p_error).text(),
                    placement : "auto",
                    container : "body",
                    trigger   : "manual" })
         .tooltip("show");
    }}, options);

    return this.each(function() {
      $(this).validate(settings);
    });
  };
}(jQuery));
