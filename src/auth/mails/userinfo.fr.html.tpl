<html>
 <head>
    <style type="text/css">
     {include file="userinfo.style"}
    </style>
 </head>
 <body>
  Bonjour <b>{$user->name}</b>,<br/>
  </br>
  Veuillez trouver vos informations de connexion {$name} ci-dessus.</br>
  <br/>
  <table>
    <tr>
      <td class="label">url : </td>
      <td><a href="{$url}">{$url}</a></td>
    </tr>
    <tr>
      <td class="label">adresse : </td>
      <td><a href="mailto:{$user->mail}">{$user->mail}</a></td>
    </tr>
    {if $password != ""}
    <tr>
      <td class="label">mot de passe : </td>
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
    Ce mail a été envoyé automatiquement.</br>
    Veuillez ne pas répondre à ce message.
  </span>
 </body>
</html>
