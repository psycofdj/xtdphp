<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

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

  static function starts_with($p_haystack, $p_needle)
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

  static function url_construct($p_path, $p_query)
  {
    if ("" != $p_query)
      return sprintf("%s?%s", $p_path, $p_query);
    return $p_path;
  }

  static function url_normalize_index($p_url)
  {
    $l_parts = explode("?", $p_url);
    $l_count = count($l_parts);

    if ((0 == $l_count) || (2 < $l_count))
      return false;

    $l_index = "index.php";
    $l_path  = $l_parts[0];
    $l_query = "";
    if (2 == $l_count)
      $l_query = $l_parts[1];
    if (true == self::ends_with($l_path, $l_index))
      $l_path = str_replace($l_index, "", $l_path);
    return self::url_construct($l_path, $l_query);
  }
}
?>