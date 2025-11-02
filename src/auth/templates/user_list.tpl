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
   $("#users").wapptable();

   $("body").on("DOMNodeInserted", "#users tbody", function() {
     $("tr td a", this).tooltip();
   });

   $("#users tbody tr td a", this).tooltip();

   $("[data-toggle~=confirmation]").wappconfirm({
     title : "{t}auth.user.list.delete.confirm{/t}"
   });

 });
</script>


<div class="container-fluid">
  <div class="row">
    <ol class="breadcrumb">
      <li><a href="/">{t}core.menu.home{/t}</a></li>
      <li><a href="/wappcore/auth/">{t}auth.menu.title{/t}</a></li>
      <li class="active">{t}auth.menu.users{/t}
        &nbsp;&nbsp;
        <a
          href="/wappcore/auth/user.php?action=add" class="btn btn-sm btn-success glyphicon glyphicon-plus"
          data-toggle="tooltip" data-placement="right" data-title="{t}auth.user.list.add{/t}"
          {perm_if action="auth/user/modify"}
          ></a>

      </li>
    </ol>
  </div> <!-- row -->

  <div class="row">
    <div class="col-md-6 col-md-offset-3 centered">
      <table id="users" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-condensed table-responsive" id="example">
        <thead>
          <tr>
            <th class="col-xs-1" style="min-width:100px">{t}auth.user.list.actions{/t}</th>
            <th class="wp-search">{t}auth.menu.mail{/t}</th>
            <th class="wp-search">{t}auth.user.list.name{/t}</th>
          </tr>
        </thead>
        <tbody>
          {foreach $users as $c_user}
            <tr>
              <td class="vert-align">
                <form method="POST">
                  <input type="hidden" name="uid" value="{$c_user->id}"/>
                  <div class="btn-group">
                    <button
                      formaction="/wappcore/auth/user.php?action=edit"
                      class="btn btn-warning btn-sm glyphicon glyphicon-pencil"
                      data-toggle="tooltip" data-placement="top" title="{t}auth.user.list.edit{/t}"
                      {perm_if action="auth/user/modify"}
                      />
                      <button
                        formaction="/wappcore/auth/user.php?action=delete"
                        class="btn btn-danger btn-sm glyphicon glyphicon-trash"
                        data-toggle="tooltip confirmation" data-placement="top" data-title="{t}auth.user.list.delete{/t}"
                        {perm_if action="auth/user/terminate"}
                        />
                  </div>
                </form>
              </td>
              <td class="vert-align">{$c_user->mail}</td>
              <td class="vert-align">{$c_user->name}</td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
  </div> <!-- row -->
</div> <!-- containter -->

