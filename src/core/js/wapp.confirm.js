/*
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

(function($) {
  // extends bootstrap confirmation plugin
  // http://ethaizone.github.io/Bootstrap-Confirmation/
  $.fn.wappconfirm = function(options) {

    var settings = $.extend({
      title          : function(p_el) {
        var l_title = $(this).data("confirm");
        if (undefined != l_title)
          return l_title;
        return $.wapp.messages.confirm.title;
      },
      modal          : false,
      btnOkLabel     : $.wapp.messages.confirm.yes,
      btnCancelLabel : $.wapp.messages.confirm.no,
      container      : "body",
      placement      : "bottom",
      onConfirm      : function(p_event, p_elem) {
        var l_form   = $(p_elem).closest("form");
        var l_action = $(p_elem).attr("formaction");
        var l_target = $(p_elem).data("formtarget");

        if (undefined != l_action)
          l_form.attr("action", l_action);
        if (undefined != l_target)
          l_form.attr("target", l_target);
        l_form.submit();
      },
      onCancel : function(p_event, p_elem) {
        $(this).confirmation('destroy');
      }
    }, options);

    return this.each(function() {
      var l_tmp = $(this).confirmation(settings);

      l_tmp.on("shown.bs.confirmation", function() {
        $(".arrow").css("top", "");
      });

    });
  };
}(jQuery));
