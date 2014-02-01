{if null == $auth_user }
  <form id="auth_menu_widget" class="navbar-form navbar-right form-inline" method="POST" action="/wappcore/auth/">
    <input type="hidden" name="action"   value="login"/>
    <div class="form-group control-group">
      <input class="required form-control" style="width:200px" type="text"     name="mail"     value="" placeholder="{t}auth.menu.mail{/t}..." />
    </div>
    <div class="form-group control-group">
      <input class="required form-control" style="width:200px" type="password" name="password" value="" placeholder="{t}auth.menu.password{/t}..." />
    </div>
    <button id="auth_btn" class="btn btn-primary" type="submit" data-trigger="manual" data-placement="bottom" data-container="body">{t}auth.menu.login{/t}</button>
  </form>
{else}
  <div class="navbar-right navbar-text">
    <span class="glyphicon glyphicon-user"></span>&nbsp;
    {$auth_user.mail} | <a href="/wappcore/auth/?action=logout">{t}auth.menu.logout{/t}</a>
  </div>
{/if}

<div id="auth_error" class="hide">
  <button type="button" class="close" onclick="$('#auth_btn').popover('hide');">&times;</button>
  <p class="text-danger"> {t}auth.menu.wrong{/t} </p>
  <a href="/wappcore/auth/recover.php">{t}auth.menu.recover{/t}</a>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $("#auth_btn").popover({
       html: true,
       content: function() { return $("#auth_error").html(); }
    });
    $("#auth_menu_widget").validate({
      errorElement : "small",
      errorClass : "text-danger small",
      highlight  : function(element, errorClass) {
         $(element).css("margin-right", "5px");
      },
      unhighlight  : function(element, errorClass) {
         $(element).css("margin-right", "0px");
      },
      submitHandler : function() {
        var l_form = $("#auth_menu_widget");
        $.ajax({
          url  : l_form.attr("action"),
          data : { "mail"     : l_form.find("input[name=mail]").val(),
                   "password" : l_form.find("input[name=password]").val(),
                   "action"   : "login" },
          type : "POST",
        }).done(function(p_data, p_status, p_xhr) {
          window.location.replace("/");
        }).fail(function(p_xhr, p_status, p_error) {
           $("#auth_btn").popover("show");
        });
      }

    });
  });
</script>
