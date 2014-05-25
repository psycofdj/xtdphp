Hello {$user->name},

Please find your credentials below.

     url: {$url}
   login: {$user->mail}
{if $password != ""}
password: {$password}
{/if}

Regards,

--
This email has been sent automatically.
Please do not respond to this message.
