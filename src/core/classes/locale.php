<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

class locale
{
  public static  $ms_locale;
  public static  $ms_localeName;
  private static $ms_data = array();

  static function addData($p_langName, $p_data)
  {
    if (false == array_key_exists($p_langName, self::$ms_data))
      self::$ms_data[$p_langName] = array();



    foreach ($p_data as $c_key => $c_value)
    {
      if (true == array_key_exists($c_key, self::$ms_data[$p_langName]))
        log::warn("core.locale", "locale key '%s' already exists for lang '%s', previous value is '%s'",
                  $c_key, $p_langName, self::$ms_data[$p_langName][$c_key]);


      if (false != ($l_key = array_search($c_value, self::$ms_data[$p_langName])))
      {
        log::warn("core.locale", "locale value '%s' already exists for lang '%s' for key '%s'",
                  $c_value, $p_langName, $l_key);
      }

      self::$ms_data[$p_langName][$c_key] = $c_value;
    }
  }

  static function init()
  {
    self::$ms_locale     = null;
    self::$ms_localeName = "??";
    self::detectLang();
  }

  static public function resolve($p_key)
  {
    if (0 === strpos($p_key, "{dnt}"))
      return substr($p_key, 5);

    $l_data = self::$ms_locale;

    if (false == array_key_exists($p_key, $l_data))
    {
      log::error("core.locale", "locale key '%s' not found for lang '%s'", $p_key, self::$ms_localeName);
      return sprintf('error: t(%s)', $p_key);
    }
    return $l_data[$p_key];
  }

  static public function detectLang()
  {
    if (true == array_key_exists("HTTP_ACCEPT_LANGUAGE", $_SERVER))
    {
      $l_str = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
      $l_data = preg_split("/[,;]+/", $l_str);
      foreach ($l_data as $c_key)
      {
        if (true == self::setLang($c_key))
          return true;
      }
    }

    log::warn("core.locale", "could not detect lang, falling back on 'fr'");
    self::setLang("fr");
    return false;
  }

  static public function getName()
  {
    return self::$ms_localeName;
  }

  static public function setLang($p_langName)
  {
    $p_langName = strtolower($p_langName);

    switch ($p_langName)
    {
    case "fr-fr":
    case "fr":
    {
      self::$ms_localeName = "fr";
      self::$ms_locale     = self::$ms_data["fr"];
      return true;
    }

    case "en-us":
    case "en":
    {
      self::$ms_localeName = "en";
      self::$ms_locale     = self::$ms_data["en"];
      return true;
    }

    default:
      return false;
    }
  }

}

function t() {
  $l_args   = func_get_args();
  $l_key    = array_shift($l_args);
  $l_format = locale::resolve($l_key);
  array_unshift($l_args, $l_format);
  return call_user_func_array("sprintf", $l_args);
}

?>