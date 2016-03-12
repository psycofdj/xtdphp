/*
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 2014
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

(function ($) {
  if (undefined == $.wapp)
    $.wapp = {};
  $.wapp.messages = {
    confirm : {
      title : "Êtes vous sûr ?",
      yes   : "Oui",
      no    : "Non"
    },
    table : {
      nofilter : "(aucun filtre)",
      empty    : "(vide)",
      notempty : "(non vide)",
      null     : "(nul)",
      notnull  : "(non nul)"
    }
  };
}(jQuery));
