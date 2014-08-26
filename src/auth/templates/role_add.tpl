<script type="text/javascript">
 $(document).ready(function() {
   $("#add").wappform();
   var l_table = $("#actions").wapptable({
     "aoColumnDefs" : [
       { "sClass"   : "", "aTargets" : "_all" }
     ]
   });

   function Role() {
     var role = this;

     role.m_checkboxes = [];

     role.getChecked = function() {
       return $("input[type=checkbox]:checked");
     };

     role.getNotOf = function(p_type) {
       return $("input[type=checkbox][data-type!=" + p_type + "]");
     };

     role.update = function(p_input) {
       var l_data = $(p_input).data("type");
       if (true == $(p_input).is(":checked")) {
         role.getNotOf(l_data).prop("disabled", true);
       }
       if (0 == role.getChecked().length) {
         role.m_checkboxes.prop("disabled", false);
       }
     };

     role.init = function() {
       role.m_checkboxes = $("input[type=checkbox]");
       role.m_checkboxes.click(function() {
         role.update($(this));
       });
       role.getChecked().each(function() {
         role.update($(this));
       });
       return role;
     };
   };

   var l_role = new Role().init();
 });
</script>



<div class="container-fluid">
  <div class="row">
    <ol class="breadcrumb">
      <li><a href="/">{t}core.menu.home{/t}</a></li>
      <li><a href="/wappcore/auth/user.php">{t}auth.menu.title{/t}</a></li>
      <li><a href="/wappcore/auth/role.php">{t}auth.menu.roles{/t}</a></li>
      <li class="active">
        {if isset($role)}
          {$role.name}
        {else}
          {t}auth.role.add.title{/t}
        {/if}
        &nbsp;&nbsp;
        <button type="submit" class="btn btn-sm btn-success glyphicon" data-form="#add" data-toggle="tooltip" data-placement="right" data-title="{t}core.save{/t}">{t}core.save{/t}</button>
      </li>
    </ol>
  </div> <!-- row -->

  {if isset($role)}
    {assign rid        $role->id}
    {assign name       $role->name}
    {assign setactions $role->sharedAuthactionList}
  {else}
    {assign rid  0}
    {assign name ""}
    {assign setactions array()}
  {/if}

  <div class="row">
    <form id="add" class="form-horizontal" action="/wappcore/auth/role.php" role="form" method="post">
      <input type="hidden" name="action" value="save"/>
      <input type="hidden" name="rid" value="{$rid}"/>

      <div class="col-md-4 col-md-offset-4 centered">
        <fieldset>
          <legend>{t}auth.role.add.roleinfo{/t}</legend>
          <div class="form-group has-feedback">
            <!-- name -->
            <label class="col-xs-2 control-label" for="name">{t}auth.role.add.name{/t}</label>
            <div class="col-xs-10">
              <input name="name" type="text" placeholder="{t}auth.role.add.name{/t}..." class="required form-control" value="{$name}"/>
              <span class="glyphicon form-control-feedback"></span>
            </div>
          </div>
        </fieldset>
        <br/><br/>
        <fieldset>
          <legend>{t}auth.role.add.actions{/t}</legend>
          <table id="actions">
            <thead>
              <th>status</th>
              <th class="text-center wp-search">name</th>
              <th class="text-center wp-filter">type</th>
            </thead>
            <tbody>
              {foreach $actions as $c_action}
                {assign checked ""}
                {foreach $setactions as $c_setaction}
                  {if $c_setaction->id == $c_action->id}
                    {assign checked "checked='checked'"}
                    {assign text "on"}
                  {/if}
                {/foreach}
                <tr>
                  <td class="text-center">
                    <input type="checkbox" name="aid[]" data-type="{$c_action->datatype}" value="{$c_action->id}" {$checked}/>
                  </td>
                  <td class="text-left">{t}{$c_action->localetag}{/t}</td>
                  <td class="text-center">{$c_action->datatype}</td>
                </tr>
              {/foreach}
            </tbody>
          </table>
        </fieldset>
      </div>

    </form>
  </div> <!-- row -->
</div> <!-- contrainer -->


