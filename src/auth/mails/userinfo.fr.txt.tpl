Bonjour {$user->name},

Veuillez trouver vos informations de connexion ci-dessus.

     adresse : {$url}
       login : {$user->mail}
{if $password != ""}
mot de passe : {$password}
{/if}

Cordiallement,

--
Ce mail a été envoyé automatiquement.
Veuillez ne pas répondre à ce message.
