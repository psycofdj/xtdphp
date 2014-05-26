<?php

class tools
{
  static function getBaseUrl()
  {
    if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off'))
      $l_url = sprintf("https://%s/", $_SERVER['HTTP_HOST']);
    else
      $l_url = sprintf("http://%s/", $_SERVER['HTTP_HOST']);
    return $l_url;
  }

  static function genPassword($p_minSize, $p_maxSize)
  {
    $l_password       = '';
    $l_desiredLength = rand($p_minSize, $p_maxSize);

    for ($c_idx = 0; $c_idx < $l_desiredLength; $c_idx++)
      $l_password .= chr(rand(32, 126));

    return $l_password;
  }
}
?>