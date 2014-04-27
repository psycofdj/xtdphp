<?php

class types
{

  public static function to_int(&$p_value)
  {
    if (false == is_numeric($p_value))
      return false;
    $p_value = (int)$p_value;
    return true;
  }

  public static function xml_to_obj(&$p_value)
  {
    if (false == ($l_value = simplexml_load_string($p_value)))
      return false;
    $p_value = $l_value;
    return true;
  }

  public static function base64_to_bin(&$p_value)
  {
    if (false == ($l_value = base64_decode($p_value, true)))
      return false;
    $p_value = $l_value;
    return true;
  }

  public static function to_uint(&$p_value)
  {
    if ((false == types::to_int($p_value)) || ($p_value < 0))
      return false;
    return true;
  }

  public static function to_float(&$p_value)
  {
    if (false == is_numeric($p_value))
      return false;
    $p_value = (float)$p_value;
    return true;
  }

  public static function to_bool(&$p_value)
  {
    if (($p_value == "true") ||
        ($p_value == "on")   ||
        ($p_value == "yes")  ||
        ($p_value == "1"))
    {
      $p_value = true;
      return true;
    }
    if (($p_value == "false") ||
        ($p_value == "off") ||
        ($p_value == "no") ||
        ($p_value == "0"))
    {
      $p_value = false;
      return true;
    }
    return false;
  }

}


?>