/*
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

(function($) {
  // extends jquery validation plugin
  // http://jqueryvalidation.org/

  $.fn.wappform = function(options) {

    options = options || {};

    var methods = {
      clear : function() {
        this.each(function() {
          $("div.form-group", this).each(function() {
            if ($(this).tooltip) {
              $(this).tooltip("destroy");
            }
            $(this).removeClass("has-error");
            $(this)
              .find("span.form-control-feedback")
              .removeClass("glyphicon-remove");
          });
        });
      }
    };

    if (methods[options]) {
      return methods[options].apply(this, Array.prototype.slice.call(arguments, 1));
    }

    var settings = $.extend({
      tooltip : {
        placement : "auto",
        container : "body"
      },
     errorClass : "has-error",
     validClass : "has-success",
     clean : function(p_form) {
       var l_validator = $(p_form).validate();
       l_validator.elements().each(function() {
         l_validator.settings.success("success", $(this));
       });
     },
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

       if (options.callbackSuccess != undefined) {
         options.callbackSuccess(p_succes, p_el);
       }
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
         .tooltip({
           title     : $(p_error).text(),
           placement : settings.tooltip.placement,
           container : settings.tooltip.container,
           trigger   : "manual"
         })
         .tooltip("show");

       if (options.callbackError != undefined) {
         options.callbackError(p_error, p_el);
       }
    }}, options);

    return this.each(function() {
      $(this).validate(settings);
    });
  };
}(jQuery));
