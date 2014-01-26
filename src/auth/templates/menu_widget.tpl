{if false == $auth_logged }
  <form class="navbar-form navbar-right form-inline" method="POST" action="/wappcore/auth.php">
    <div class="form-group">
      <input type="hidden" name="action"   value="login" />
      <input class="form-control" style="width:200px" type="text"     name="mail"     value="" placeholder="{t}auth.menu.mail{/t}..." />
      <input class="form-control" style="width:200px" type="password" name="password" value="" placeholder="{t}auth.menu.password{/t}..." />
    </div>
    <button class="btn btn-default" type="submit">{t}auth.menu.login{/t}</button>
    <a class="navbar-link" href="/wappcore/auth.php">{t}auth.menu.recover{/t}</a>
  </form>
{else}
  <i class="icon-user icon-white">​</i>​
  {$auth_user.name} | <a href="/wappcore/auth.php?action=logout">
{/if}
