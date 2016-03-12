{*
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
*}
Bonjour {$user->name},

Veuillez trouver vos informations de connexion {$__brand_name} ci-dessus.

     adresse : {$__base_url}
       login : {$user->mail}
{if $password != ""}
mot de passe : {$password}
{/if}

Cordiallement,

--
Ce mail a été envoyé automatiquement.
Veuillez ne pas répondre à ce message.
