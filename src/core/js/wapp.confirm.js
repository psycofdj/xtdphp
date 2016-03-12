/*
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 2014
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
        if (undefined != l_action)
          l_form.attr("action", l_action);
        l_form.submit();
      },
      onCancel : function(p_event, p_elem) {
        $(this).confirmation('destroy');
      }
    }, options);

    return this.each(function() {
      var l_tmp = $(this).confirmation(settings);
      if (false == settings.modal)
        return;

      l_tmp.on("shown.bs.confirmation", function() {
        var l_data = $(this).data("bs.confirmation");
        var l_el = $(this).parents(".modal").attr("aria-describedby");
        var l_widget = $("#"+l_el);
        l_data = $.extend($.fn.confirmation.Constructor.DEFAULTS, l_data);


        l_widget.attr("style", "");
        l_widget.css("display", "block");


        if (l_data.placement == "right")
        {
          l_widget.css("top", $(this).offset().top);
          l_widget.css("left", $(this).offset().left + $(this).outerWidth());
        } else if (l_data.placement == "left")
        {
          l_widget.css("top", $(this).offset().top + ($(this).outerHeight() / 2) - (l_widget.outerHeight() / 2));
          l_widget.css("left", $(this).offset().left - l_widget.outerWidth());
        }
        else if (l_data.placement == "top")
        {
          l_widget.css("top", $(this).offset().top - l_widget.outerHeight());
          l_widget.css("left", $(this).offset().left + ($(this).outerWidth() / 2) - (l_widget.outerWidth() / 2));
        }
        else if (l_data.placement == "bottom")
        {
          l_widget.css("top", $(this).offset().top + $(this).outerHeight());
          l_widget.css("left", $(this).offset().left + ($(this).outerWidth() / 2) - (l_widget.outerWidth() / 2));
        }
      });

    });
  };
}(jQuery));
