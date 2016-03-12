/*
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

(function ($) {
  if (undefined == $.wapp)
    $.wapp = {};
  $.wapp.messages = {
    confirm : {
      title : "Are you sure ?",
      yes   : "Yes",
      no    : "No"
    },
    table : {
      nofilter : "(no filter)",
      empty    : "(empty)",
      notempty : "(not empty)",
      null     : "(null)",
      notnull  : "(not null)"
    }
  };
}(jQuery));
