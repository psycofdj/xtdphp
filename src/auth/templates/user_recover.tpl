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
   $(".alert").alert();
   $("#recover").wappform();
 });
</script>

<div class="container-fluid">
  <div class="row">
    <ol class="breadcrumb">
      <li><a href="/">{t}core.menu.home{/t}</a></li>
      <li><a href="/wappcore/auth/user.php">{t}auth.menu.title{/t}</a></li>
      <li class="active">{t}auth.menu.recover{/t}</li>
    </ol>
  </div>


  <div class="row">
    <div class="col-md-4 col-md-offset-3 centered">

      {if $status == "ok"}
      <div class="alert alert-success text-center">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {t var1=$user->name var2="$mail"}auth.user.recover.ok{/t}
      </div>
      {/if}

      {if $status == "notfound"}
      <div class="alert alert-danger text-center">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {t var="$mail"}auth.user.recover.error.notfound{/t}
      </div>
      {/if}

      <form id="recover" class="form-horizontal" action="/wappcore/auth/user.php" role="form" method="post">
        <input type="hidden" name="action" value="recover"/>
        <div class="form-group has-feedback">
          <!-- mail -->
          <label class="col-sm-2 control-label" for="email">{t}core.mail{/t}</label>
          <div class="col-sm-10">
            <input name="email" type="email" placeholder="{t}core.mail{/t}..." class="required form-control" value="{$mail}"/>
            <span class="glyphicon form-control-feedback"></span>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-10 col-sm-offset-2">
            <button type="submit" class="btn btn-primary">{t}auth.user.recover.submit{/t}</button>
          </div>
        </div>
      </form>

    </div> <!-- col -->
  </div> <!-- row -->

</div> <!-- container -->

