/*
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 2014
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

jQuery.fn.outerHTML = function(s) {
  return (s)
  ? this.before(s).remove()
  : jQuery("<p>").append(this.eq(0).clone()).html();
}
