<html>
 <head>
    <style type="text/css">
     {include file="userinfo.style"}
    </style>
 </head>
 <body>
  Hello <b>{$user->name}</b>,<br/>
  </br>
  Please find your {$name} credentials below.</br>
  <br/>
  <table>
    <tr>
      <td class="label">url:</td>
      <td><a href="{$url}">{$url}</a></td>
    </tr>
    <tr>
      <td class="label">login:</td>
      <td><a href="mailto:{$user->mail}">{$user->mail}</a></td>
    </tr>
    {if $password != ""}
    <tr>
      <td class="label">password:</td>
      <td>{$password}</td>
    </tr>
    {/if}
    <tr>
      <td colspan="2" class="logo">
        <br/><br/>
        <img src="cid:brand"/>
      </td>
    </tr>
  </table>

  <span class="footer">
    <br/><br/>
    This email has been sent automatically.</br>
    Please do not respond to this message.
  </span>
 </body>
</html>