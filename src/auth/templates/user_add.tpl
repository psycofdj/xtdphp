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
   $("*[data-toggle=tooltip]").tooltip({ container: 'body' });

   $("#add").wappform();

   $("body").on("click", ".current_role", function() {
     $(this).parents(".form-group").remove();
   });

 $("body").on("click", ".available_role", function() {
    var l_idx       = $("#current").data("count");
    var l_perm      = $("<input type='hidden' name='perm[]' />");
    var l_permRole  = $("<input type='hidden' />");
    var l_permData  = $("<input type='hidden' />");
    var l_val       = $(this).parents(".form-group").find("select option:selected").val() || "";
    var l_roleID    = $(this).parents(".form-group").find("input[name=role]").val();
    var l_group     = $(this).parents(".form-group").clone();

    $(l_perm).val(l_idx);
    $(l_permRole).attr("name",  "perm_" + l_idx + "_role");
    $(l_permRole).attr("value", l_roleID);
    $(l_permData).attr("name",  "perm_" + l_idx + "_data");
    $(l_permData).attr("value", l_val);

    $("button.available_role", l_group)
      .removeClass("available_role")
      .removeClass("glyphicon-plus")
      .removeClass("btn-success")
      .addClass("current_role")
      .addClass("glyphicon-minus")
      .addClass("btn-warning");
    $("select", l_group).prop("disabled", true);
    $("select option[value=" + l_val + "]", l_group).prop("selected", true);
    $(l_group)
       .append(l_perm)
       .append(l_permRole)
       .append(l_permData);
    $("#current").append(l_group);
    $("#current").data("count", l_idx + 1);
 });

});
</script>


<div class="container-fluid">
  <div class="row">
    <ol class="breadcrumb">
      <li><a href="/">{t}core.menu.home{/t}</a></li>
      <li><a href="/wappcore/auth/user.php">{t}auth.menu.title{/t}</a></li>
      <li><a href="/wappcore/auth/user.php">{t}auth.menu.users{/t}</a></li>
      <li class="active">
        {if isset($user)}
          {$user.name}
        {else}
          {t}auth.user.add.title{/t}
        {/if}
        &nbsp;&nbsp;
        <button type="submit" class="btn btn-sm btn-success glyphicon glyphicon-floppy-save" data-form="#add" data-toggle="tooltip" data-placement="right" data-title="{t}core.save{/t}"></button>
      </li>
    </ol>
  </div> <!-- row -->


  {if isset($user)}
    {assign uid           $user->id}
    {assign mail          $user->mail}
    {assign name          $user->name}
    {assign requiered     ""}
    {assign perms         $user->ownAuthuserAuthpermList}
    {assign userresources $user->ownAuthuserAuthresourceList}
  {else}
    {assign uid           0}
    {assign mail          ""}
    {assign name          ""}
    {assign requiered     "required"}
    {assign perms         array()}
    {assign userresources array()}
  {/if}

  <div class="row">
    <form id="add" class="form-horizontal" action="/wappcore/auth/user.php" role="form" method="post">
      <input type="hidden" name="action" value="save"/>
      <input type="hidden" name="uid" value="{$uid}"/>

      <div class="col-md-4 centered">
        <fieldset>
          <legend>{t}auth.user.add.userinfo{/t}</legend>
          <div class="form-group has-feedback">
            <!-- mail -->
            <label class="col-xs-5 control-label" for="email">{t}core.mail{/t}</label>
            <div class="col-xs-7">
              <input name="email" type="email" placeholder="{t}core.mail{/t}..." class="required form-control" value="{$mail}"/>
              <span class="glyphicon  form-control-feedback"></span>
            </div>
          </div>

          <div class="form-group has-feedback">
            <!-- name -->
            <label class="col-xs-5 control-label" for="name">{t}auth.user.add.name{/t}</label>
            <div class="col-xs-7">
              <input name="name" type="text" placeholder="{t}auth.user.add.name{/t}..." class="required form-control" value="{$name}"/>
              <span class="glyphicon form-control-feedback"></span>
            </div>
          </div>

          <div class="form-group has-feedback">
            <!-- password -->
            <!-- fake input for webkit bug : http://stackoverflow.com/questions/15738259/disabling-chrome-autofill -->
            <input type="text" class="hidden"/>
            <label class="col-xs-5 control-label" for="password">{t}auth.user.add.password{/t}</label>
            <div class="col-xs-7">
              <input id="password" name="password" type="password" placeholder="{t}auth.user.add.password{/t}..." class="{$requiered} form-control"/>
              <span class="glyphicon  form-control-feedback"></span>
            </div>
          </div>

          <div class="form-group has-feedback">
            <!-- password confirm -->
            <label class="col-xs-5 control-label" for="passwordc">{t}auth.user.add.password_confirm{/t}</label>
            <div class="col-xs-7">
              <input id="passwordc" type="password" placeholder="{t}auth.user.add.password{/t}..." class="{$requiered} form-control"/>
              <span class="glyphicon  form-control-feedback"></span>
            </div>
          </div>

          {foreach $resources as $c_res name=res}
            <div class="form-group">
              <input type="hidden" name="resource[]" value="{$smarty.foreach.res.index}"/>
              <input type="hidden" name="resource_{$smarty.foreach.res.index}_name" value="{$c_res->getName()}"/>
              <label class="col-xs-5 control-label">{t var="{$c_res->getTag()}"}auth.user.add.default{/t}</label>
              <div class="col-xs-7">
                <select class="form-control" name="resource_{$smarty.foreach.res.index}_id">
                  {foreach $c_res->generate() as $c_value}
                    {assign selected ""}
                    {foreach $userresources as $c_setres}
                      {if ($c_res->getName() == $c_setres->name) && ($c_setres->value == $c_value.id)}
                        {assign selected "selected='selected'"}
                      {/if}
                    {/foreach}
                    <option value="{$c_value.id}" {$selected}>{$c_value.label}</option>
                  {/foreach}
                </select>
              </div>
            </div>
          {/foreach}

        </fieldset>
      </div> <!-- col-md-4 -->

      <div class="col-md-4 centered">
        <fieldset id="current" data-count="{count($perms)}">
          <legend>{t}auth.user.add.roles.set{/t}</legend>
          {foreach $perms as $c_perm name=perm}
          {assign c_role $c_perm->authrole}
          <div class="row form-group">
            <input type="hidden" name="perm[]" value='{$smarty.foreach.perm.index}'/>
            <input type="hidden" name="perm_{$smarty.foreach.perm.index}_role" value='{$c_role->id}'/>
            <input type="hidden" name="perm_{$smarty.foreach.perm.index}_data" value='{$c_perm->data}'/>
            <div class="col-xs-2 text-right">
              <button type="button" class="btn btn-default btn-warning glyphicon glyphicon-minus current_role"></button>
            </div>
            <div class="col-xs-4 form-text">
              {$c_role->name}
            </div>
            {if $c_role->datatype}
            <div class="col-xs-6">
              <select class="form-control" disabled="disabled">
                {foreach $resources[$c_role->datatype]->generate() as $c_data}
                {assign selected ""}
                {if $c_perm->data == $c_data.id}
                {assign selected "selected='selected'"}
                {/if}
                <option value="{$c_data.id}" {$selected}>{$c_data.label}</option>
                {/foreach}
              </select>
            </div>
            {/if}
          </div> <!-- form-group -->
          {/foreach}
        </fieldset>
      </div> <!-- col-md-4 -->
    </form>

    <div class="col-md-4 centered">
      <fieldset>
        <legend>{t}auth.user.add.roles.all{/t}</legend>
        {foreach $roles as $c_role}
        <div class="row form-group">
          <input name="role" value="{$c_role->id}" type="hidden"/>
          <div class="col-xs-2 text-right">
            <button type="button" class="btn btn-default btn-success glyphicon glyphicon-plus available_role"></button>
          </div>
          <div class="col-xs-4 form-text">
            {$c_role->name}
          </div>
          {if $c_role->datatype}
          <div class="col-xs-6">
            <select class="form-control">
              {foreach $resources[$c_role->datatype]->generate() as $c_data}
              <option value="{$c_data.id}">{$c_data.label}</option>
              {/foreach}
            </select>
          </div>
          {/if}
        </div> <!-- form-group -->
        {/foreach}

      </fieldset>
    </div> <!-- col-md-4 -->
  </div> <!-- row -->

</div> <!-- contrainer -->


