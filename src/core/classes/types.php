<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

class types
{

  public static function is_array(&$p_value)
  {
    return is_array($p_value);
  }

  public static function is_mail(&$p_value)
  {
    return filter_var($p_value, FILTER_VALIDATE_EMAIL);
  }


  public static function to_int(&$p_value)
  {
    if (false == is_numeric($p_value))
      return false;
    $p_value = (int)$p_value;
    return true;
  }


  public static function to_date(&$p_value)
  {
    if (false == ($l_value = DateTime::createFromFormat("Y-m-d", $p_value)))
      return false;
    $p_value = $l_value;
    return true;
  }

  public static function xml_to_obj(&$p_value)
  {
    if (false === ($l_value = simplexml_load_string($p_value)))
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