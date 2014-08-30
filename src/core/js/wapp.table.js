/* Set the defaults for DataTables initialisation */
$.extend( true, $.fn.dataTable.defaults, {
  "sDom":
  "<'row'<'col-xs-6'l><'col-xs-6'f>r>" +
    "t" +
    "<'row'<'col-xs-6'i><'col-xs-6'p>>"
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
  }
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


$.fn.dataTableExt.oApi.fnGetServerColumnsData = function (oSettings, p_colIdx) {

  var l_results;
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


$.fn.dataTableExt.oApi.fnGetColumnData = function ( oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty ) {
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
    var sValue = aData[iColumn];

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
}


function fnCreateSelect(aData, p_colIdx, p_settings)
{
  var r = "";
  var i;
  var iLen = aData.length;

  r += '<option value="__any__">' + p_settings.sAllCellFilterLabel + '</option>';
  if (p_settings.bFilterAllowEmpty)
    r += '<option value="">' + p_settings.sEmptyCellFilterLabel + '</option>';
  if (p_settings.bFilterAllowNotEmpty)
    r += '<option value="__notempty__">' + p_settings.sNotEmptyCellFilterLabel + '</option>';
  if (p_settings.bFilterAllowNull)
    r += '<option value="__null__">' + p_settings.sNullCellFilterLabel + '</option>';
  if (p_settings.bFilterAllowNotNull)
    r += '<option value="__notnull__">' + p_settings.sNotNullCellFilterLabel + '</option>';

  for (i = 0 ; i < iLen ; i++ )
  {
    var l_node  = aData[i] || "__null__";
    var l_label = l_node;

    if ((undefined != p_settings.aoColumns) &&
        (undefined != p_settings.aoColumns[p_colIdx]) &&
        (undefined != p_settings.aoColumns[p_colIdx].aFilterLabels) &&
        (undefined != p_settings.aoColumns[p_colIdx].aFilterLabels[l_node]))
      l_label = p_settings.aoColumns[p_colIdx].aFilterLabels[l_node];

    if (l_label == "__null__")
      l_label = p_settings.sNullCellFilterLabel;

    r += '<option value="' + l_node + '">' + l_label + '</option>';
  }
  return r;
}


function escapeRegExp(str) {
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}



/**
 * Creates a bootstrap compatible datatable.
 * See : http://datatables.net/ for construct options
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
        var settings = $.extend({
            "sFilterPlace"             : "thead",
            "bAutoWidth"               : false,
            "bColFilter"               : true,  // enable automatique creation of ciltering widgets
            "bCookie"                  : true,  // use cookie to save filtering options
            "dCookieTime"              : 365,   // when use cookie, expire time of the cookie
            "sAllCellFilterLabel"      : $.wapp.messages.table.nofilter,
            "sEmptyCellFilterLabel"    : $.wapp.messages.table.empty,
            "sNotEmptyCellFilterLabel" : $.wapp.messages.table.notempty,
            "sNullCellFilterLabel"     : $.wapp.messages.table.null,
            "sNotNullCellFilterLabel"  : $.wapp.messages.table.notnull,
            "bFilterAllowNull"         : false,
            "bFilterAllowNotNull"      : false,
            "bFilterAllowNotEmpty"     : false,
            "bFilterAllowEmpty"        : false,
            "aoColumnDefs"             : [ { "sClass": "text-center", "aTargets": "_all" } ],
            "fnDrawCallback"           : function(p_settings) {
              $("form", this).wappform();
              if (false == $.wapp.mobile.isAny()) {
                $("[data-toggle~=tooltip]", this).tooltip({ container: "body" });
                $("[data-toggle~=confirmation]", this).wappconfirm();
              }
              $(this).trigger("wapptable.loaded");
            }

        }, options);

    // return this.each(function() {
    //   // create datatable
      var l_tableID = $(this).prop("id") || null;

      if (null == l_tableID)
      {
        alert("wapptables need table id");
        return;
      }

      var l_cookieName  = "#" + l_tableID + "_length";
      var l_cookieValue = $.cookie(l_cookieName);

      if ((true == settings.bCookie) && (undefined != l_cookieValue))
        settings.iDisplayLength = parseInt(l_cookieValue);

      var l_table = $(this).dataTable(settings);

      // 1.
      $("tbody", l_table).on("click", "tr", function() {
        var l_current = l_table.data("current-row");

        if (null != l_current)
          l_current.removeClass("active");
        $(this).addClass("active");
        l_table.data("current-row", $(this));
      });


      // 2.
      $("tbody", l_table).on("click", "tr td", function() {
        var l_current = l_table.data("current-cell");
        if (null != l_current)
          l_current.removeClass("warning");
        $(this).addClass("warning");
        l_table.data("current-cell", $(this));
      });

      // 3.
      if (false == settings.bColFilter)
        return;


      var l_head        = $("thead", l_table);
      var l_row         = $("<tr></tr>");
      var l_nbSearch    = 0;

      if (settings.sScrollY)
      {
        var l_wrapper = $("#" + l_table.prop("id") + "_wrapper");
        l_head = $("div.dataTables_scrollHead table > thead", l_wrapper);
      }

      $(l_cookieName).each(function() {
        $(this).change(function() {
          l_cookieValue = $(this).find(":selected").text();
          $.cookie(l_cookieName, l_cookieValue, {expires : settings.dCookieTime});
        });
      });


      $("> tr > th", l_head).each(function(p_colIndex) {
        var l_cell        = $("<th></th>");
        var l_cookieName  = "#" + l_tableID + ".wappt-col" + p_colIndex;
        var l_cookieValue = $.cookie(l_cookieName);
        if (true == $(this).hasClass("wp-search"))
        {
          var l_input       = $("<input class='form-control input-sm' type='text' style='width:100%;'/>");
          var l_title       = $(this).text();
          var l_settings    = $.fn.dataTable.defaults;
          var l_placeholder = $(this).data("placeholder") || null;

          $(l_input).keyup(function () {
            l_table.fnFilter($(this).val(), p_colIndex);
            if ($(this).prop("disabled") == false)
              $.cookie(l_cookieName, $(this).val(), {expires : settings.dCookieTime});
            if ($(this).val() != "")
              $(this).addClass("input-danger");
            else
              $(this).removeClass("input-danger");
          });


          if ((true == settings.bCookie) && (l_cookieValue != undefined) && (l_cookieValue != ""))
            $(l_input).val(l_cookieValue).keyup();

          if ((null == l_placeholder) && (0 != l_title.length))
            l_placeholder = l_settings.oLanguage.sSearch + " " + l_title.toLowerCase() + " ...";
          if ("none" != l_placeholder)
            $(l_input).attr("placeholder", l_placeholder);
          l_cell.append(l_input);
          l_nbSearch += 1;
        }
        else if (true == $(this).hasClass("wp-filter"))
        {
          var l_data;
          var l_select = $("<select style='width:100%;' class='form-control input-sm'/>");

          if (undefined != settings.sAjaxSource)
            l_data = l_table.fnGetServerColumnsData(p_colIndex);
          else
            l_data = l_table.fnGetColumnData(p_colIndex, true, true, false);

          l_select.html(fnCreateSelect(l_data, p_colIndex, settings));

          l_select.change(function() {
            var l_val = $(this).val();
            if (undefined != settings.sAjaxSource)
            {
              if (l_val == "__any__")
                l_table.fnFilter("__any__", p_colIndex, false, false, false);
              else if (l_val == "__notempty__")
                l_table.fnFilter("^.+$", p_colIndex, true, false, false);
              else if (l_val == "__null__")
                l_table.fnFilter("__null__", p_colIndex, false, false, false);
              else if (l_val == "__notnull__")
                l_table.fnFilter("__notnull__", p_colIndex, false, false, false);
              else
                l_table.fnFilter("^" + escapeRegExp($(this).val()) + "$", p_colIndex, true, false, false);
            }
            else
            {
              if (l_val == "__any__")
                l_table.fnFilter(".*", p_colIndex, true, false, false);
              else if (l_val == "__notempty__")
                l_table.fnFilter("^.+$", p_colIndex, true, false, false);
              else if (l_val == "__null__")
                l_table.fnFilter("^$", p_colIndex, true, false, false);
              else if (l_val == "__notnull__")
                l_table.fnFilter("^.+$", p_colIndex, true, false, false);
              else
                l_table.fnFilter("^" + escapeRegExp($(this).val()) + "$", p_colIndex, true, false, false);
            }


            if ($(this).prop("disabled") == false)
              $.cookie(l_cookieName, $(this).val(), {expires : settings.dCookieTime});

            if ($(this).val() != "__any__")
              $(this).addClass("input-danger");
            else
              $(this).removeClass("input-danger");
          });

          if ((true == settings.bCookie) && (l_cookieValue != undefined))
          {
            var l_opt = $("option[value='" + l_cookieValue + "']", l_select);
            if (l_opt.length > 0)
              l_select.val(l_cookieValue);
            if (l_cookieValue != "__any__")
              l_select.change();
          }
          l_cell.append(l_select);
          l_cell.css("text-align", "center");
          l_nbSearch += 1;
        }
        l_row.append(l_cell);
      });

      if (0 != l_nbSearch)
      {
        var l_tfoot = null;
        if (settings.sFilterPlace != "thead")
        {
          l_place = $("<tfoot></tfoot>");
          l_place.append(l_row);
          l_table.append(l_place);
        }
        else
          l_head.append(l_row);
        l_table.filters = l_row;
      }


      return l_table;
  };

}(jQuery));
