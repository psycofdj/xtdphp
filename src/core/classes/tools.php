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

  function starts_with($p_haystack, $p_needle)
  {
    return $p_needle === "" || strpos($p_haystack, $p_needle) === 0;
  }

  static function ends_with($p_haystack, $p_needle)
  {
    return $p_needle === "" || substr($p_haystack, -strlen($p_needle)) === $p_needle;
  }

  static function genPassword($p_minSize, $p_maxSize)
  {
    $l_password       = '';
    $l_desiredLength = rand($p_minSize, $p_maxSize);

    for ($c_idx = 0; $c_idx < $l_desiredLength; $c_idx++)
      $l_password .= chr(rand(32, 126));

    return $l_password;
  }

  static function array_key_map($p_array, $p_callback)
  {
    $l_result = array();
    foreach ($p_array as $c_key => $c_value)
      $p_callback($l_result, $c_key, $c_value);
    return $l_result;
  }
}
?>