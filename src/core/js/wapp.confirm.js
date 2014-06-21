(function($) {
  // extends bootstrap confirmation plugin
  // http://ethaizone.github.io/Bootstrap-Confirmation/
  $.fn.wappconfirm = function(options) {

    var settings = $.extend({
      title          : $.wapp.messages.confirm.title,
      btnOkLabel     : $.wapp.messages.confirm.yes,
      btnCancelLabel : $.wapp.messages.confirm.no,
      container      : "body",
      popover        : true,
      singleton      : true,
      placement      : "bottom",
      onConfirm      : function(p_event, p_elem) {
        var l_form = $(p_elem).parents("form");
        l_form.attr("action", $(p_elem).attr("formaction"));
        l_form.submit();
      }
    }, options);

    return this.each(function() {
      $(this).confirmation(settings);
    });
  };
}(jQuery));
