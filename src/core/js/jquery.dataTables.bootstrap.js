/* Set the defaults for DataTables initialisation */
$.extend( true, $.fn.dataTable.defaults, {
  "sDom":
  "<'row'<'col-xs-6'l><'col-xs-6'f>r>" +
    "t" +
    "<'row'<'col-xs-6'i><'col-xs-6'p>>"
} );


/* Default class modification */
$.extend( $.fn.dataTableExt.oStdClasses, {
  "sWrapper": "dataTables_wrapper form-inline",
  "sFilterInput": "form-control input-sm",
  "sLengthSelect": "form-control input-sm"
} );

// In 1.10 we use the pagination renderers to draw the Bootstrap paging,
// rather than  custom plug-in
if ( $.fn.dataTable.Api ) {
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
else {
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
  if (bFiltered == true) aiRows = oSettings.aiDisplay;
  // use all rows
  else aiRows = oSettings.aiDisplayMaster; // all row numbers

  // set up data array
  var asResultData = new Array();


  for (var i=0,c=aiRows.length; i<c; i++) {
    iRow = aiRows[i];
    var aData = this.fnGetData(iRow);
    var sValue = aData[iColumn];

    if ((sValue.indexOf(">") > -1) && (sValue.indexOf("<") > -1))
      sValue = $(sValue).text().trim();

    // ignore empty values?
    if (bIgnoreEmpty == true && sValue.length == 0) continue;

    // ignore unique values?
    else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1) continue;

    // else push the value onto the result data array
    else asResultData.push(sValue);
  }

  return asResultData;
}


function fnCreateSelect(aData, sEmptyLabel)
{
  var r='<option value="all"></option>', i, iLen=aData.length;
  for ( i=0 ; i<iLen ; i++ )
  {
    var l_node = aData[i];
    var l_label = l_node;
    if (l_label == "")
      l_label = sEmptyLabel;
    r += '<option value="'+l_node+'">'+l_label+'</option>';
  }
  return r;
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
      "bColFilter"            : true,  // enable automatique creation of ciltering widgets
      "bForceColFilter"       : false, // display filters even if not many data
      "bCookie"               : true,  // use cookie to save filtering options
      "sEmptyCellFilterLabel" : "(empty)"
    }, options);

    return this.each(function() {
      // create datatable

      // 0.
      if ((false == settings.bForceColFilter) &&
          (((settings.aaData) && (settings.aaData.length < 11)) ||
           ($("tr", $(this)).length < 11)))
      {
        settings.bLengthChange = false;
        settings.bPaginate = false;
        settings.bFilter = false;
        settings.bColFilter = false;
      }

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

      var l_tfoot       = $("<tfoot><tr></tr></tfoot>");
      var l_row         = $("tr", l_tfoot);
      var l_nbSearch    = 0;
      var l_cookieName  = undefined;
      var l_cookieValue = undefined;
      var l_tableID     = l_table.prop("id") || null;

      if (null == l_tableID)
      {
        alert("wapptables need table id");
        return;
      }

      $("thead > tr > th", l_table).each(function(p_colIndex) {
        var l_cell = $("<th></th>");
        var l_cookieName = "#" + l_tableID + ".wappt-col" + p_colIndex;
        var l_cookieValue = $.cookie(l_cookieName);

        if (true == $(this).hasClass("wp-search"))
        {
          var l_input       = $("<input class='form-control' type='text'/>");
          var l_title       = $(this).text();
          var l_settings    = $.fn.dataTable.defaults;
          var l_placeholder = $(this).data("placeholder") || null;

          $(l_input).keyup(function () {
            l_table.fnFilter($(this).val(), p_colIndex);
            $.cookie(l_cookieName, $(this).val());
          });

          if ((true == settings.bCookie) && (l_cookieValue != undefined))
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
          var l_select = $("<select style='width:100%;' class='form-control'/>");

          l_select.html(fnCreateSelect(l_table.fnGetColumnData(p_colIndex, true, true, false), settings.sEmptyCellFilterLabel));
          l_select.change(function() {
            var l_val = $(this).val();
            if (l_val != "all")
              l_table.fnFilter("^" + $(this).val() + "$", p_colIndex, true, false, false);
            else
              l_table.fnFilter("^.*$", p_colIndex, true, false, false);
            $.cookie(l_cookieName, $(this).val());
          });

          if ((true == settings.bCookie) && (l_cookieValue != undefined))
          {
            var l_opt = $("option[value='" + l_cookieValue + "']", l_select);
            if (l_opt.length > 0)
              l_select.val(l_cookieValue).change();
          }

          l_cell.append(l_select);
          l_cell.css("text-align", "center");
          l_nbSearch += 1;
        }
        l_row.append(l_cell);
      });
      if (0 != l_nbSearch)
        l_table.append(l_tfoot);
    });
  };

}(jQuery));
