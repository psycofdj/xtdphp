Hello {$user->name},

Please find your {$__brand_name} credentials below.

 address: {$__base_url}
   login: {$user->mail}
{if $password != ""}
password: {$password}
{/if}

Regards,

--
This email has been sent automatically.
Please do not respond to this message.
