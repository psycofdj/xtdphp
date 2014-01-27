{if null == $auth_user }
  <form id="auth_menu_widget" class="navbar-form navbar-right form-inline" method="POST" action="/wappcore/auth/auth.php">
      <input type="hidden" name="action"   value="login"/>
    <div class="form-group control-group">
      <input class="required form-control" style="width:200px" type="text"     name="mail"     value="" placeholder="{t}auth.menu.mail{/t}..." />
    </div>
    <div class="form-group control-group">
      <input class="required form-control" style="width:200px" type="password" name="password" value="" placeholder="{t}auth.menu.password{/t}..." />
    </div>
    <button class="btn btn-primary" type="submit">{t}auth.menu.login{/t}</button>
    <a href="/wappcore/auth/recover.php">{t}auth.menu.recover{/t}</a>
  </form>
{else}
  <div class="navbar-right navbar-text">
    <span class="glyphicon glyphicon-user"></span>&nbsp;
    {$auth_user.mail} | <a href="/wappcore/auth/auth.php?action=logout">{t}auth.menu.logout{/t}</a>
  </div>
{/if}

<script type="text/javascript">
  $(document).ready(function() {
    $("#auth_menu_widget").validate({
      errorElement : "small",
      errorClass : "text-danger text-small",
      highlight  : function(element, errorClass) {
         $(element).css("margin-right", "5px");
      },
      unhighlight  : function(element, errorClass) {
         $(element).css("margin-right", "0px");
      }
    });
  });
</script>
