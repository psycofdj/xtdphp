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
        <th class="wp-search">{t}auth.menu.mail{/t}</th>
        <th class="wp-search">{t}auth.userlist.name{/t}</th>
        <th class="wp-search">{t}auth.userlist.role{/t}</th>
        <th class="col-xs-1">{t}auth.userlist.actions{/t}</th>
      </tr>
    </thead>
    <tbody>
      {foreach $auth_users as $c_user}
      <tr>
        <td class="vert-align">{$c_user->mail}</td>
        <td class="vert-align">{$c_user->name}</td>
        <td class="vert-align">
          {foreach $auth->getRoles() as $c_role}
          {assign "class" "glyphicon glyphicon-ok text-success"}
          {if false == $c_role->validFor($c_user->role)}
          {assign "class" "glyphicon glyphicon-remove text-danger"}
          {/if}
          <span class="{$class}"></span> {t}{$c_role->m_tag}{/t} <br/>
          {/foreach}

        </td>
        <td class="text-center vert-align">
          {if $auth->hasRole("auth.roles.user.write", $auth_user->role)}
          <a href="/wappcore/auth/?action=useredit&uid={$c_user->id}" class="btn btn-warning btn-xs" title="{t}auth.userlist.tooltips.edit{/t}">
            <span class="glyphicon glyphicon-pencil"/>
          </a>
          <a href="/wappcore/auth/?action=userdelete&uid={$c_user->id}" class="btn btn-danger btn-xs " title="{t}auth.userlist.tooltips.delete{/t}">
            <span class="glyphicon glyphicon-trash"/>
          </a>
          {/if}
        </td>
      </tr>
      {/foreach}
    </tbody>
  </table>
</div>

