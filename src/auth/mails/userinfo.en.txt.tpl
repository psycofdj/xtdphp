{*
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
*}
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
