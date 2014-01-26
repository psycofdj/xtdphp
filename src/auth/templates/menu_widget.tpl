{if false == $auth_logged }
  <form method="POST" action="/wappcore/auth.php">
    <input type="hidden"   name="action"   value="login" />
    <input type="text"     name="mail"     value="" placeholder="{t}core.auth.mail{/t}" />
    <input type="password" name="password" value="" placeholder="{t}core.auth.password{/t}" />
    <input type="submit"                   value="{t}core.auth.login{/t}"    />
  </form>
  <a href="/wappcore/auth.php">{t}core.auth.recover{/t}</a>
{else}
  <i class="icon-user icon-white">​</i>​
  {$auth_user.name} | <a href="/wappcore/auth.php?action=logout">
{/fi}
