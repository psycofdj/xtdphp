
<br/>
<ul>
  <li> Au dela d'un millier de lignes, il faut passer par "aaData" sans quoi la construction est lente</li>
  <li> Au dela de 10 000 lignes, il ne faut pas utiliser "wp-filter"</li>
  <li> Un test avec 100000 montre que cela reste raisonnable </li>
</ul>

<script type="text/javascript">
 $(document).ready(function() {
   $("#example").wapptable({
     "aaData" : {include file="file:[examples]array.data.tpl"}
   });
 });
</script>


<div style="padding:50px">
<table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-condensed table-responsive" id="example">
  <thead>
    <tr>
      <th class="wp-search">Rendering engine</th>
      <th class="wp-search">Browser</th>
      <th>Platform(s)</th>
      <th>Engine version</th>
      <th class="wp-filter">CSS grade</th>
      </tr>
    </thead>
</table>
</div>
