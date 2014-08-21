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
      btnOkLabel     : $.wapp.messages.confirm.yes,
      btnCancelLabel : $.wapp.messages.confirm.no,
      container      : "body",
      popout         : true,
      singleton      : true,
      placement      : "bottom",
      onConfirm      : function(p_event, p_elem) {
        var l_form   = $(p_elem).parents("form");
        var l_action = $(p_elem).attr("formaction");
        if (undefined != l_action)
          l_form.attr("action", l_action);
        l_form.submit();
      },
      onCancel : function(p_event, p_elem) {
        $(this).confirmation('hide');
      }
    }, options);

    return this.each(function() {
      $(this).confirmation(settings);
    });
  };
}(jQuery));
