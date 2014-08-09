(function ($) {
  if (undefined == $.wapp)
    $.wapp = {};

  $.wapp.mobile = {
    isAndroid: function() {
      return (null != navigator.userAgent.match(/Android/i));
    },
    isBlackberry: function() {
      return (null != navigator.userAgent.match(/BlackBerry/i));
    },
    isIos: function() {
      return (null != navigator.userAgent.match(/iPhone|iPad|iPod/i));
    },
    isOpera: function() {
      return (null != navigator.userAgent.match(/Opera Mini/i));
    },
    isWindows: function() {
      return (null != navigator.userAgent.match(/IEMobile/i));
    },
    isAny: function() {
      return ($.wapp.mobile.isAndroid()    ||
              $.wapp.mobile.isBlackberry() ||
              $.wapp.mobile.isIos()        ||
              $.wapp.mobile.isOpera()      ||
              $.wapp.mobile.isWindows());
    }
  };
}(jQuery));