<script type="text/javascript">
 $(document).ready(function() {
   $("#auth_list").wapptable({
    "aaData" : [
      {foreach $auth_users as $c_user}
        [ "{$c_user->mail}", "{$c_user->name}", "{$c_user->role}" ],
        [ "{$c_user->mail}", "{$c_user->name}", "{$c_user->role}" ],
        [ "{$c_user->mail}", "{$c_user->name}", "{$c_user->role}" ],
        [ "{$c_user->mail}", "{$c_user->name}", "{$c_user->role}" ],
        [ "{$c_user->mail}", "{$c_user->name}", "{$c_user->role}" ],
        [ "{$c_user->mail}", "{$c_user->name}", "{$c_user->role}" ],
        [ "{$c_user->mail}", "{$c_user->name}", "{$c_user->role}" ],
      {/foreach}
    ]
   });
 });
</script>


<div style="padding:50px">
  <table id="auth_list" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-condensed table-responsive" id="example">
    <thead>
      <tr>
        <th>{t}auth.userlist.actions{/t}</th>
        <th class="wp-search">{t}auth.menu.mail{/t}</th>
        <th class="wp-search">{t}auth.userlist.name{/t}</th>
        <th class="wp-search">{t}auth.userlist.role{/t}</th>
      </tr>
    </thead>
  </table>
</div>

