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
