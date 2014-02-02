<script type="text/javascript">
 $(document).ready(function() {
   $("#auth_list").wapptable();

   $("body").on("DOMNodeInserted", "#auth_list tbody", function() {
     $("tr td a", this).tooltip();
   });
   $("#auth_list tbody tr td a", this).tooltip();
 });
</script>


<div style="padding:50px">
  <table id="auth_list" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-condensed table-responsive" id="example">
    <thead>
      <tr>
        <th class="wp-search">{t}auth.rolelist.tag{/t}</th>
        <th class="wp-search">{t}auth.rolelist.description{/t}</th>
      </tr>
    </thead>
    <tbody>
      {foreach $auth->getRoles() as $c_role}
      <tr>
        <td> {$c_role->m_tag} </td>
        <td> {t}{$c_role->m_tag}{/t} </td>
      </tr>
      {/foreach}
    </tbody>
  </table>
</div>

