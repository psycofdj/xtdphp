{*
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
*}
{if null == $auth_user }
  <form id="auth_menu_widget" class="navbar-form form-inline text-center" style="margin-right:0px;margin-left:0px;" method="POST" action="/wappcore/auth/">
    <input type="hidden" name="action"   value="login"/>
    <input class="required form-control input-sm" type="email"    name="mail"     value="" placeholder="{t}auth.menu.mail{/t}..."     />
    <input class="required form-control input-sm" type="password" name="password" value="" placeholder="{t}auth.menu.password{/t}..." />
    <button id="auth_btn" class="btn btn-primary" type="submit" data-trigger="manual" data-placement="bottom" data-container="body">{t}auth.menu.login{/t}</button>
  </form>
{else}
  <div class="navbar-right navbar-text">
    <span class="glyphicon glyphicon-user"></span>&nbsp;
    {$auth_user.mail} | <a href="/wappcore/auth/?action=logout">{t}auth.menu.logout{/t}</a>
  </div>
{/if}


<div class="modal" id="auth_error" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <button class="pull-right btn btn-default glyphicon glyphicon-remove" data-dismiss="modal"></button>
        <h4 class="modal-title text-danger text-center">{t}auth.menu.fail{/t}</h4>
      </div>
      <div class="modal-body text-center">
        <p>{t}auth.menu.fail.credentials{/t}</p>
        <a class="btn btn-primary" href="/wappcore/auth/user.php?action=recover">{t}auth.menu.recover{/t}</a>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
   var l_modal = $("#auth_error").detach();
   l_modal.appendTo($("body"));

    $("#auth_menu_widget").validate({
      rules: {
        password: { minlength: 8 }
      },

     success : function(p_succes, p_el) {
       $(p_el).tooltip("hide");
     },

     errorPlacement : function(p_error, p_el) {
       p_el.tooltip("destroy");
       p_el.tooltip({ title     : $(p_error).text(),
                      placement : "bottom",
                      container : ".navbar",
                      trigger   : "manual" });
       p_el.tooltip("show");
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
           $("#auth_error").modal({ backdrop:false });
        });
      }
    });
  });
</script>
