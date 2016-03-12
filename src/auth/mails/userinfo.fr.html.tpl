{*
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
*}
<html>
 <head>
    <style type="text/css">
     {include file="mail.style"}
    </style>
 </head>
 <body>
  Bonjour <b>{$user->name}</b>,<br/>
  </br>
  Veuillez trouver vos informations de connexion {$__brand_name} ci-dessus.</br>
  <br/>
  <table>
    <tr>
      <td class="label">adresse : </td>
      <td><a href="{$__base_url}">{$__base_url}</a></td>
    </tr>
    <tr>
      <td class="label">login : </td>
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
        <img src="cid:__brand_logo"/>
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

