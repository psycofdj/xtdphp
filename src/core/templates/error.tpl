<script type="text/javascript">
  $("document").ready(function() {
    $(".alert button").click(function() {
      window.location = "/";
    });
  });
</script>


{if isset($error_tpl)}
  {include file="$error_tpl"}
{else}
  <div class="container-fluid">
    <br/></br>
    <div class="col-md-4 col-md-offset-4 centered">
      <div class="alert alert-danger alert-dismissable text-center">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <span class="glyphicon glyphicon-warning-sign"></span> {$error_message}
      </div>
    </div>
  </div>
{/if}
