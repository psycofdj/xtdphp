{*
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
*}

<script type="text/javascript">
 $(document).ready(function() {
   $("#test").wapptable({
     bCookie         : true,
     bServerSide     : true,
     sAjaxSource     : "/wappcore/test.php?action=test",
     fnCreatedRow    : function(nRow, aData, iDataIndex) {
       var l_href = $.sprintf("/client/?action=view&cid=%d", aData[3]);
       var l_link = $.sprintf("<a href='%s'>%s</a>", l_href, aData[2]);
       $("td:eq(2)", nRow).html(l_link);
     }
   });
 });
</script>

<table id="test" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-condensed table-responsive" >
  <thead>
    <tr>
      <th class="wp-filter">col1</th>
      <th class="wp-search">col2</th>
      <th class="wp-search">col3</th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>
