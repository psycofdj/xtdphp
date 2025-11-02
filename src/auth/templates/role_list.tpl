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
   $("#roles").wapptable();

   function decorate(p_parent) {
     $("[data-toggle~=tooltip]", p_parent).tooltip({ container: "body" });
     $("[data-toggle~=confirmation]", p_parent).wappconfirm({
       title : "{t}auth.role.list.delete.confirm{/t}",
     });
   }

   $("body").on("DOMNodeInserted", "#roles tbody", function() {
      decorate($(this));
   });
   decorate("body");
 });
</script>


<div class="container-fluid">
  <div class="row">
    <ol class="breadcrumb">
      <li><a href="/">{t}core.menu.home{/t}</a></li>
      <li><a href="/wappcore/auth/">{t}auth.menu.title{/t}</a></li>
      <li class="active">{t}auth.menu.roles{/t}
        &nbsp;&nbsp;
        <a
          href="/wappcore/auth/role.php?action=add" class="btn btn-sm btn-success glyphicon glyphicon-plus"
          data-toggle="tooltip" data-placement="right" data-title="{t}auth.role.list.add{/t}"
          {perm_if action="auth/role/modify"}
          ></a>
      </li>
    </ol>
  </div> <!-- row -->

  <div class="row">
    <div class="col-md-6 col-md-offset-3 centered">
      <table id="roles" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover table-condensed table-responsive" id="example">
        <thead>
          <tr>
            <th style="width:100px;" class="col-xs-1"></th>
            <th class="wp-search text-center">{t}auth.role.list.name{/t}</th>
            <th class="wp-search text-center">{t}auth.role.list.data{/t}</th>
          </tr>
        </thead>
        <tbody>
          {foreach $roles as $c_role}
            <tr>
              <td class="vert-align">
                <form method="POST">
                  <input type="hidden" name="rid" value="{$c_role->id}"/>
                  <div class="btn-group">
                    <button
                      formaction="/wappcore/auth/role.php?action=edit"
                      class="btn btn-warning btn-sm glyphicon glyphicon-pencil"
                      data-toggle="tooltip" data-placement="top" title="{t}auth.role.list.edit{/t}"
                      {perm_if action="auth/role/modify"}
                      />
                      <button
                        formaction="/wappcore/auth/role.php?action=delete"
                        class="btn btn-danger btn-sm glyphicon glyphicon-trash"
                        data-toggle="tooltip confirmation" data-placement="top" data-title="{t}auth.role.list.delete{/t}"
                        {perm_if action="auth/role/terminate"}
                        />
                  </div>
                </form>
              </td>
              <td class="vert-align text-center">{$c_role->name}</td>
              <td class="vert-align text-center">{$c_role->datatype}</td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
  </div> <!-- row -->
</div> <!-- containter -->

