/*
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

/* Set the defaults for DataTables initialisation */
$.extend( true, $.fn.dataTable.defaults, {
  "sDom":
  "t" +
    "<'row'<'col-xs-6'l><'col-xs-6'p>r>" +
    "<'row'<'col-xs-6'i><'col-xs-6'f>>"
} );

/* Default class modification */
$.extend( $.fn.dataTableExt.oStdClasses, {
  "sWrapper"      : "dataTables_wrapper form-inline",
  "sFilterInput"  : "form-control input-sm",
  "sLengthSelect" : "form-control input-sm"
} );

// In 1.10 we use the pagination renderers to draw the Bootstrap paging,
// rather than  custom plug-in
if ($.fn.dataTable.Api)
{
  $.fn.dataTable.defaults.renderer = 'bootstrap';
  $.fn.dataTable.ext.renderer.pageButton.bootstrap = function ( settings, host, idx, buttons, page, pages ) {
    var api = new $.fn.dataTable.Api( settings );
    var classes = settings.oClasses;
    var lang = settings.oLanguage.oPaginate;
    var btnDisplay, btnClass;

    var attach = function( container, buttons ) {
      var i, ien, node, button;
      var clickHandler = function ( e ) {
        e.preventDefault();
        if ( e.data.action !== 'ellipsis' ) {
          api.page( e.data.action ).draw( false );
        }
      };

      for ( i=0, ien=buttons.length ; i<ien ; i++ ) {
        button = buttons[i];

        if ( $.isArray( button ) ) {
          attach( container, button );
        }
        else {
          btnDisplay = '';
          btnClass = '';

          switch ( button ) {
          case 'ellipsis':
            btnDisplay = '&hellip;';
            btnClass = 'disabled';
            break;

          case 'first':
            btnDisplay = lang.sFirst;
            btnClass = button + (page > 0 ?
                                 '' : ' disabled');
            break;

          case 'previous':
            btnDisplay = lang.sPrevious;
            btnClass = button + (page > 0 ?
                                 '' : ' disabled');
            break;

          case 'next':
            btnDisplay = lang.sNext;
            btnClass = button + (page < pages-1 ?
                                 '' : ' disabled');
            break;

          case 'last':
            btnDisplay = lang.sLast;
            btnClass = button + (page < pages-1 ?
                                 '' : ' disabled');
            break;

          default:
            btnDisplay = button + 1;
            btnClass = page === button ?
              'active' : '';
            break;
          }

          if ( btnDisplay ) {
            node = $('<li>', {
              'class': classes.sPageButton+' '+btnClass,
              'aria-controls': settings.sTableId,
              'tabindex': settings.iTabIndex,
              'id': idx === 0 && typeof button === 'string' ?
                settings.sTableId +'_'+ button :
                null
            } )
              .append( $('<a>', {
                'href': '#'
              } )
                       .html( btnDisplay )
                     )
              .appendTo( container );

            settings.oApi._fnBindAction(
              node, {action: button}, clickHandler
            );
          }
        }
      }
    };

    attach(
      $(host).empty().html('<ul class="pagination"/>').children('ul'),
      buttons
    );
  };
}
else
{
  // Integration for 1.9-
  $.fn.dataTable.defaults.sPaginationType = 'bootstrap';

  /* API method to get paging information */
  $.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
  {
    return {
      "iStart":         oSettings._iDisplayStart,
      "iEnd":           oSettings.fnDisplayEnd(),
      "iLength":        oSettings._iDisplayLength,
      "iTotal":         oSettings.fnRecordsTotal(),
      "iFilteredTotal": oSettings.fnRecordsDisplay(),
      "iPage":          oSettings._iDisplayLength === -1 ?
        0 : Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
      "iTotalPages":    oSettings._iDisplayLength === -1 ?
        0 : Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
    };
  };

  /* Bootstrap style pagination control */
  $.extend( $.fn.dataTableExt.oPagination, {
    "bootstrap": {
      "fnInit": function( oSettings, nPaging, fnDraw ) {
        var oLang = oSettings.oLanguage.oPaginate;
        var fnClickHandler = function ( e ) {
          e.preventDefault();
          if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
            fnDraw( oSettings );
          }
        };

        $(nPaging).append(
          '<ul class="pagination">'+
            '<li class="prev disabled"><a href="#">&larr; '+oLang.sPrevious+'</a></li>'+
            '<li class="next disabled"><a href="#">'+oLang.sNext+' &rarr; </a></li>'+
            '</ul>'
        );
        var els = $('a', nPaging);
        $(els[0]).bind( 'click.DT', { action: "previous" }, fnClickHandler );
        $(els[1]).bind( 'click.DT', { action: "next" }, fnClickHandler );
      },

      "fnUpdate": function ( oSettings, fnDraw ) {
        var iListLength = 5;
        var oPaging = oSettings.oInstance.fnPagingInfo();
        var an = oSettings.aanFeatures.p;
        var i, ien, j, sClass, iStart, iEnd, iHalf=Math.floor(iListLength/2);

        if ( oPaging.iTotalPages < iListLength) {
          iStart = 1;
          iEnd = oPaging.iTotalPages;
        }
        else if ( oPaging.iPage <= iHalf ) {
          iStart = 1;
          iEnd = iListLength;
        } else if ( oPaging.iPage >= (oPaging.iTotalPages-iHalf) ) {
          iStart = oPaging.iTotalPages - iListLength + 1;
          iEnd = oPaging.iTotalPages;
        } else {
          iStart = oPaging.iPage - iHalf + 1;
          iEnd = iStart + iListLength - 1;
        }

        for ( i=0, ien=an.length ; i<ien ; i++ ) {
          // Remove the middle elements
          $('li:gt(0)', an[i]).filter(':not(:last)').remove();

          // Add the new list items and their event handlers
          for ( j=iStart ; j<=iEnd ; j++ ) {
            sClass = (j==oPaging.iPage+1) ? 'class="active"' : '';
            $('<li '+sClass+'><a href="#">'+j+'</a></li>')
              .insertBefore( $('li:last', an[i])[0] )
              .bind('click', function (e) {
                e.preventDefault();
                oSettings._iDisplayStart = (parseInt($('a', this).text(),10)-1) * oPaging.iLength;
                fnDraw( oSettings );
              } );
          }

          // Add / remove disabled classes from the static elements
          if ( oPaging.iPage === 0 ) {
            $('li:first', an[i]).addClass('disabled');
          } else {
            $('li:first', an[i]).removeClass('disabled');
          }

          if ( oPaging.iPage === oPaging.iTotalPages-1 || oPaging.iTotalPages === 0 ) {
            $('li:last', an[i]).addClass('disabled');
          } else {
            $('li:last', an[i]).removeClass('disabled');
          }
        }
      }
    }
  } );
}

/*
 * TableTools Bootstrap compatibility
 * Required TableTools 2.1+
 */
if ( $.fn.DataTable.TableTools ) {
  // Set the classes that TableTools uses to something suitable for Bootstrap
  $.extend( true, $.fn.DataTable.TableTools.classes, {
    "container": "DTTT btn-group",
    "buttons": {
      "normal": "btn btn-default",
      "disabled": "disabled"
    },
    "collection": {
      "container": "DTTT_dropdown dropdown-menu",
      "buttons": {
        "normal": "",
        "disabled": "disabled"
      }
    },
    "print": {
      "info": "DTTT_print_info modal"
    },
    "select": {
      "row": "active"
    }
  } );

  // Have the collection use a bootstrap compatible dropdown
  $.extend( true, $.fn.DataTable.TableTools.DEFAULTS.oTags, {
    "collection": {
      "container": "ul",
      "button": "li",
      "liner": "a"
    }
  } );
}

$.fn.dataTableExt.oApi.setColFilter = function(oSettings, sInput, iColumn, bRegex) {
  oSettings.aoPreSearchCols[iColumn].sSearch = sInput;
  oSettings.aoPreSearchCols[iColumn].bRegex  = bRegex;
};

$.fn.dataTableExt.oApi.fnGetServerColumnsData = function (oSettings, p_colIdx) {
  var l_results = [];

  if ((undefined != oSettings.aoColumns[p_colIdx]) &&
      (undefined != oSettings.aoColumns[p_colIdx].aFilterLabels))
  {
    $.each(oSettings.aoColumns[p_colIdx].aFilterLabels, function(p_key, p_value) {
      l_results.push(p_key);
    });
    return l_results;
  }

  var l_name = oSettings.aoColumns[p_colIdx].mData;
  var l_data = {
    colIdx  : p_colIdx,
    colName : l_name
  };

  $.ajax({
    dataType : "json",
    data     : l_data,
    url      : oSettings.sAjaxSource,
    async    : false,
    success  : function(p_data, p_status, p_xhr) {
     l_results = p_data;
    }
  });

  return l_results;
};


$.fn.dataTableExt.oApi.fnGetColumnData = function (oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty ) {
  // check that we have a column id
  if ( typeof iColumn == "undefined" ) return new Array();
  // by default we only want unique data
  if ( typeof bUnique == "undefined" ) bUnique = true;
  // by default we do want to only look at filtered data
  if ( typeof bFiltered == "undefined" ) bFiltered = true;
  // by default we do not want to include empty values
  if ( typeof bIgnoreEmpty == "undefined" ) bIgnoreEmpty = true;

  // list of rows which we're going to loop through
  var aiRows;

  // use only filtered rows
  if (bFiltered == true)
    aiRows = oSettings.aiDisplay;
  else   // use all rows
    aiRows = oSettings.aiDisplayMaster; // all row numbers

  // set up data array
  var asResultData = new Array();


  for (var i=0,c=aiRows.length; i<c; i++) {
    iRow = aiRows[i];
    var aData = this.fnGetData(iRow);

    if (false == Array.isArray(aData)) {
      var aCols  = Object.keys(aData);
      var sValue = aData[aCols[iColumn]];
    } else {
      var sValue = aData[iColumn];
    }



    if ((sValue.indexOf(">") > -1) && (sValue.indexOf("<") > -1))
      sValue = $(sValue).text().trim();

    // ignore empty values?
    if (bIgnoreEmpty == true && sValue.length == 0)
      continue;
    // ignore unique values?
    else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1)
      continue;
    // else push the value onto the result data array
    else
      asResultData.push(sValue);
  }

  return asResultData;
};


function escapeRegExp(str) {
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}



function WappFilter(p_target, p_th, p_table, p_tableID, p_colIdx, p_settings) {
  var self = p_target;

  self.m_th        = p_th;
  self.m_table     = p_table;
  self.m_tableID   = p_tableID;
  self.m_colIdx    = p_colIdx;
  self.m_settings  = p_settings;
  self.m_cookieSet = false;
  self.m_name      = "#" + self.m_tableID + ".wappt-col" + self.m_colIdx;
  self.m_cell      = $("<th></th>");


  self.__getElement = function() {
    return undefined;
  };

  self.__isDisabled = function() {
    return true == self.__getElement().prop("disabled");
  };

  self.__isValueValid = function(p_value) {
    return true;
  };

  self.__isValueDanger = function(p_value) {
    return (0 != p_value.length);
  };

  self.__filterValue = function(p_value) {
    self.m_table.setColFilter(p_value, self.m_colIdx, false);
  };

  self.__getDefaultValue = function(p_value) {
    return "";
  };

  self.isCookieSet = function() {
    return self.m_cookieSet;
  };

  self.enable = function() {
    self.__getElement().prop("disabled", false);
  };

  self.disable = function() {
    self.__getElement().prop("disabled", true);
  };

  self.save = function(p_value) {
    if (false == self.m_settings.bCookie) return;
    if (true == self.__isDisabled())      return;
    $.cookie(self.m_name, p_value);
  };

  self.update = function(p_value) {
    if (false == self.__isValueValid(p_value)) return;

    var l_el = self.__getElement();
    l_el.val(p_value);

    if (self.__isValueDanger(p_value)) {
      l_el.addClass("input-danger");
    } else {
      l_el.removeClass("input-danger");
    }
    self.__filterValue(p_value);
    self.save(p_value);
    $(self).trigger("wapptable.filter.updated");
  };

  self.reset = function(p_value) {
    self.update(self.__getDefaultValue());
  };

  self.load = function() {
    if (false == self.m_settings.bCookie) return;
    var l_value = $.cookie(self.m_name) || undefined;
    if (undefined != l_value) {
      self.update(l_value);
      self.m_cookieSet = true;
    }
  };

  self.getCell = function() {
    return self.m_cell;
  };

  self.__init = function() {
  };

  self.init = function() {
    self.__init();
    self.m_cell.append(self.__getElement());
    self.load();
  };
};

function WappFilterSelect(p_th, p_table, p_tableID, p_colIdx, p_settings) {
  var self = this;

  WappFilter(self, p_th, p_table, p_tableID, p_colIdx, p_settings);
  self.m_isRegExp = p_th.hasClass("wp-regexp");
  self.m_select   = $("<select style='width:100%;' class='form-control input-sm'/>");

  self.__getDefaultValue = function(p_value) {
    return "__any__";
  };

  self.__isValueDanger = function(p_value) {
    return ("__any__" != p_value);
  };

  self.__isValueValid = function(p_value) {
    var l_opt = $("option[value='" + p_value + "']", self.m_select);
    return (0 != l_opt.length);
  };

  self.__getElement = function() {
    return self.m_select;
  };

  self.__filterValue = function(p_value) {
    if (p_value == "__any__") {
      if (undefined != self.m_settings.sAjaxSource) {
        self.m_table.setColFilter("__any__", self.m_colIdx, false);
      } else {
        self.m_table.setColFilter(".*", self.m_colIdx, true);
      }
    } else if (p_value == "__notempty__") {
      self.m_table.setColFilter("__notempty__", self.m_colIdx, false);
    } else if (p_value == "__empty__") {
      self.m_table.setColFilter("__empty__", self.m_colIdx, false);
    } else if (p_value == "__null__") {
      self.m_table.setColFilter("__null__", self.m_colIdx, false);
    } else if (p_value == "__notnull__") {
      self.m_table.setColFilter("__notnull__", self.m_colIdx, false);
    } else {
      if (false == self.m_isRegExp) {
        self.m_table.setColFilter("^" + escapeRegExp(p_value) + "$", self.m_colIdx, true);
      } else {
        self.m_table.setColFilter("^" + escapeRegExp(p_value) + ".*", self.m_colIdx, true);
      }
    }
  };

  self.getColumnsValues = function() {
    if (undefined != self.m_settings.sAjaxSource) {
      return self.m_table.fnGetServerColumnsData(self.m_colIdx);
    }
    return self.m_table.fnGetColumnData(self.m_colIdx, true, true, false);
  };

  self.allowValues = function()
  {
    if ((undefined != self.m_settings.aoColumns) &&
        (undefined != self.m_settings.aoColumns[p_colIdx]) &&
        (undefined != self.m_settings.aoColumns[p_colIdx].bFilterAllowValues))
      return self.m_settings.aoColumns[p_colIdx].bFilterAllowValues;
    return true;
  };

  self.getLabel  = function(p_type) {
    if ((undefined != self.m_settings.aoColumns) &&
        (undefined != self.m_settings.aoColumns[p_colIdx])) {
      if ((p_type == "__any__") &&
          (undefined != self.m_settings.aoColumns[p_colIdx].sAllCellFilterLabel))
        return self.m_settings.aoColumns[p_colIdx].sAllCellFilterLabel;
      if ((p_type == "__notnull__") &&
          (undefined != self.m_settings.aoColumns[p_colIdx].sNotNullCellFilterLabel))
        return self.m_settings.aoColumns[p_colIdx].sNotNullCellFilterLabel;
      if ((p_type == "__null__") &&
          (undefined != self.m_settings.aoColumns[p_colIdx].sNullCellFilterLabel))
        return self.m_settings.aoColumns[p_colIdx].sNullCellFilterLabel;
      if ((p_type == "__empty__") &&
          (undefined != self.m_settings.aoColumns[p_colIdx].sEmptyCellFilterLabel))
        return self.m_settings.aoColumns[p_colIdx].sEmptyCellFilterLabel;
      if ((p_type == "__notempty__") &&
          (undefined != self.m_settings.aoColumns[p_colIdx].sNotEmptyCellFilterLabel))
        return self.m_settings.aoColumns[p_colIdx].sNotEmptyCellFilterLabel;
    }
    if (p_type == "__any__") {
      return self.m_settings.sAllCellFilterLabel;
    } else if  (p_type == "__empty__") {
      return self.m_settings.sEmptyCellFilterLabel;
    } else if  (p_type == "__null__") {
      return self.m_settings.sNullCellFilterLabel;
    } else if  (p_type == "__notnull__") {
      return self.m_settings.sNotNullCellFilterLabel;
    } else if  (p_type == "__notempty__") {
      return self.m_settings.sNotEmptyCellFilterLabel;
    }
    return "";
  };

  self.isAllowed = function(p_type)
  {
    if ((undefined != self.m_settings.aoColumns) &&
        (undefined != self.m_settings.aoColumns[p_colIdx])) {
      if ((p_type == "__any__") &&
          (undefined != self.m_settings.aoColumns[p_colIdx].bFilterAllowAny))
        return self.m_settings.aoColumns[p_colIdx].bFilterAllowAny;
      if ((p_type == "__notnull__") &&
          (undefined != self.m_settings.aoColumns[p_colIdx].bFilterAllowNotNull))
        return self.m_settings.aoColumns[p_colIdx].bFilterAllowNotNull;
      if ((p_type == "__null__") &&
          (undefined != self.m_settings.aoColumns[p_colIdx].bFilterAllowNull))
        return self.m_settings.aoColumns[p_colIdx].bFilterAllowNull;
      if ((p_type == "__empty__") &&
          (undefined != self.m_settings.aoColumns[p_colIdx].bFilterAllowEmpty))
        return self.m_settings.aoColumns[p_colIdx].bFilterAllowEmpty;
      if ((p_type == "__notempty__") &&
          (undefined != self.m_settings.aoColumns[p_colIdx].bFilterAllowNotEmpty))
        return self.m_settings.aoColumns[p_colIdx].bFilterAllowNotEmpty;
    }
    if (p_type == "__any__") {
      return self.m_settings.bFilterAllowAny;
    } else if  (p_type == "__empty__") {
      return self.m_settings.bFilterAllowEmpty;
    } else if  (p_type == "__null__") {
      return self.m_settings.bFilterAllowNull;
    } else if  (p_type == "__notnull__") {
      return self.m_settings.bFilterAllowNotNull;
    } else if  (p_type == "__notempty__") {
      return self.m_settings.bFilterAllowNotEmpty;
    }
    return false;
  };

  self.createSelectOptions = function(p_values)
  {
    var r = "";
    var i;
    var iLen = p_values.length;
    var l_specials = [ "__any__", "__empty__", "__notempty__", "__null__", "__notnull__"];

    for (c_idx in l_specials) {
      l_item = l_specials[c_idx];
      if (true == self.isAllowed(l_item)) {
        r += '<option value="' + l_item + '">' + self.getLabel(l_item) + '</option>';
      }
    }

    if (self.allowValues()) {
      for (i = 0 ; i < iLen ; i++ )
      {
        var l_node  = p_values[i] || "__null__";
        var l_label = l_node;

        if ((undefined != self.m_settings.aoColumns) &&
            (undefined != self.m_settings.aoColumns[p_colIdx]) &&
            (undefined != self.m_settings.aoColumns[p_colIdx].aFilterLabels) &&
            (undefined != self.m_settings.aoColumns[p_colIdx].aFilterLabels[l_node]))
          l_label = self.m_settings.aoColumns[p_colIdx].aFilterLabels[l_node];

        if (l_label == "__null__")
          l_label = self.getLabel("__null__");

        r += '<option value="' + l_node + '">' + l_label + '</option>';
      }
    }
    return r;
  };

  self.__init = function() {
    var l_values = self.getColumnsValues();
    self.m_select.html(self.createSelectOptions(l_values));
    self.m_select.change(function() {
      self.update($(this).val());
      $(this).trigger("wapptable.filter.edited");
      self.m_table.api().draw();
    });
    self.m_cell.css("text-align", "center");
  };

  self.init();
}

function WappFilterInput(p_th, p_table, p_tableID, p_colIdx, p_settings) {
  var self = this;

  WappFilter(self, p_th, p_table, p_tableID, p_colIdx, p_settings);
  self.m_input = $("<input class='form-control input-sm' type='text' style='width:100%;'/>");

  self.__getElement = function() {
    return self.m_input;
  };

  self.initPlaceholder = function() {
    var l_placeholder = self.m_th.data("placeholder") || "";
    var l_title       = p_th.text()                   || "";
    if ((0 == l_placeholder.length) && (0 != l_title.length)) {
      l_placeholder = $.sprintf("%s %s ...", self.m_settings.oLanguage.sSearch, l_title);
      self.m_input.attr("placeholder", l_placeholder);
    }
  };

  self.__init = function() {
    self.initPlaceholder();
    self.m_input.keyup(function() {
      self.update($(this).val());
      $(this).trigger("wapptable.filter.edited");
      self.m_table.api().draw();
    });
  };

  self.init();
}


function WappFilterNull(p_th, p_table, p_tableID, p_colIdx, p_settings) {
  var self = this;

  WappFilter(self, p_th, p_table, p_tableID, p_colIdx, p_settings);
  self.__getElement = function() {
    return self.m_cell;
  };
  self.init();
}



/**
 * Creates a bootstrap compatible datatable.
 * See : http://legacy.datatables.net/ for construct options
 *
 * 0. Disable filters, paginate and length change for small tables
 * 1. Add live click handler on rows
 *    we store active row in table data
 * 2. Add live click handler on cells
 *    we store active cell in table data
 * 3  Add search input and select according to classes wp-filter avec wp-search
 *    of thead headers.
 */
(function($) {
  $.fn.wapptable = function(options) {
    var self        = this;
    self.m_id       = self.prop("id") || null;
    self.m_settings = $.extend($.fn.dataTable.defaults, {
      "sFilterPlace"             : "thead",
      "bAutoWidth"               : false,
      "bColFilter"               : true,  // enable automatique creation of ciltering widgets
      "bCookie"                  : false,  // use cookie to save filtering options
      "dCookieTime"              : 365,   // when use cookie, expire time of the cookie
      "sAllCellFilterLabel"      : $.wapp.messages.table.nofilter,
      "sEmptyCellFilterLabel"    : $.wapp.messages.table.empty,
      "sNotEmptyCellFilterLabel" : $.wapp.messages.table.notempty,
      "sNullCellFilterLabel"     : $.wapp.messages.table.null,
      "sNotNullCellFilterLabel"  : $.wapp.messages.table.notnull,
      "bFilterAllowAny"          : true,
      "bFilterAllowNull"         : false,
      "bFilterAllowNotNull"      : false,
      "bFilterAllowNotEmpty"     : false,
      "bFilterAllowEmpty"        : false,
      "bFilterAllowValues"       : true,
      "aoColumnDefs"             : [ { "sClass": "text-center", "aTargets": "_all" } ],
      "fnDrawCallback"           : function(p_settings) {
        $(this).trigger("wapptable.loaded");
      }
    });
    self.m_settings = $.extend(self.m_settings, options);

    self.wappize = function(p_target) {
      if (false == $.wapp.mobile.isAny()) {
        $("[data-toggle~=tooltip]", p_target).tooltip({ container: "body" });
        $("[data-toggle~=confirmation]", p_target).wappconfirm();
      }
    };

    self.load = function() {
      if (false == self.m_settings.bCookie) return;
      var l_name  = $.sprintf("#%s_length", self.m_id);
      var l_value = $.cookie(l_name) || undefined;
      if (undefined != l_value) {
        self.m_settings.iDisplayLength = parseInt(l_value);
      }
    };

    self.highlighRow = function(p_row) {
      var l_current = self.m_table.data("current-row");
      if (null != l_current)
        l_current.removeClass("active");
      p_row.addClass("active");
      self.m_table.data("current-row", p_row);
    };


    self.highlighCell = function(p_cell) {
      var l_current = self.m_table.data("current-cell");
      if (null != l_current)
        l_current.removeClass("warning");
      p_cell.addClass("warning");
      self.m_table.data("current-cell", p_cell);
    };

    self.create = function() {
      self.m_table = $(self).dataTable(self.m_settings);
    };

    self.bind = function() {
      $("tbody", self.m_table).on("click", "tr", function() {
        self.highlighRow($(this));
      });

      $("tbody", self.m_table).on("click", "tr td", function() {
        self.highlighCell($(this));
      });

      self.m_table.on("wapptable.loaded", function() {
        self.wappize(this);
      });

      var l_name = $.sprintf("#%s_length", self.m_id);
      $(l_name).change(function() {
        var l_value = $("option:selected", this).val();
        $.cookie(l_name, l_value);
      });
    };

    self.getHead = function() {
      var l_head  = $("thead", self.m_table);
      if (self.m_settings.sScrollY)
      {
        var l_wrapper = $("#" + self.m_id + "_wrapper");
        l_head = $("div.dataTables_scrollHead table > thead", l_wrapper);
      }
      return l_head;
    };


    self.deleteFilters = function() {
      $(".wapptable-row-filter", self.m_table).remove();
    };

    self.initFilters = function() {
      if (false == self.m_settings.bColFilter) return false;

      var l_redraw  = false;
      var l_head    = self.getHead();
      var l_row     = $("<tr></tr>");
      var l_tfoot   = null;
      var l_filters = [];

      l_row.addClass("wapptable-row-filter");

      $("> tr > th", l_head).each(function(p_colIndex) {
        var l_cell   = $("<th></th>");
        var l_filter = null;



        if (true == $(this).hasClass("wp-search")) {
          l_filter = new WappFilterInput($(this), self.m_table, self.m_id, p_colIndex, self.m_settings);
        } else if (true == $(this).hasClass("wp-filter")) {
          l_filter = new WappFilterSelect($(this), self.m_table, self.m_id, p_colIndex, self.m_settings);
        } else {
          l_filter = new WappFilterNull($(this), self.m_table, self.m_id, p_colIndex, self.m_settings);
        }

        if (null != l_filter) {
          l_cell = l_filter.getCell();
        }
        l_row.append(l_cell);
        l_filters.push(l_filter);
        l_redraw = l_redraw || l_filter.isCookieSet();
      });

      if (self.m_settings.sFilterPlace != "thead")
      {
        l_place = $("<tfoot></tfoot>");
        l_place.append(l_row);
      }
      else {
        l_head.append(l_row);
      }
      self.m_table.filters = $(l_filters);

      return l_redraw;
    };


    self.init = function() {
      if (self.m_id == null) {
        throw "wapptables need table id attribute";
      }
      self.load();
      self.create();
      self.bind();

      var l_redraw = self.initFilters();
      self.m_table.filter = function(p_idx) {
        return self.m_table.filters[p_idx];
      };
      self.m_table.highlighCell = self.highlighCell;
      self.m_table.highlighRow  = self.highlighRow;
      if (l_redraw) {
        self.m_table.api().draw();
      };

      self.m_table.destroy = function() {
        self.deleteFilters();
        self.m_table.fnDestroy();
      };


    };

    self.init();
    return self.m_table;
  };

}(jQuery));
