<?php

require_once(dirname(__FILE__) . "/../../config.php");
require_once(__BASE_DIR__      . "/wappcore/locales/fr.php");
require_once(__BASE_DIR__      . "/wappcore/locales/en.php");

class locale
{
  public static $ms_locale;
  public static $ms_localeName;

  static function init()
  {
    self::$ms_locale     = null;
    self::$ms_localeName = "??";
    self::detectLang();
  }

  static public function resolve($p_key)
  {
    $l_keys = explode(":", $p_key);
    $l_data = self::$ms_locale;

    if (false == array_key_exists($p_key, $l_data))
    {
      log::error("locale key '%s' not found", $p_key);
      return 'error: ø("' . $p_key . '")';
    }
    return $l_data[$p_key];
  }

  static public function detectLang()
  {
    $l_str = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
    $l_data = preg_split("/[,;]+/", $l_str);

    foreach ($l_data as $c_key)
    {
      if (true == self::setLang($c_key))
        return true;
    }

    log::warn("could not detect lang, falling back on 'fr'");
    self::setLang("fr");
    return false;
  }

  static public function getName()
  {
    return self::$ms_localeName;
  }

  static public function setLang($p_lang)
  {
    global $g_localeFr;
    global $g_localeEn;

    $p_lang = strtolower($p_lang);

    switch ($p_lang)
    {
    case "fr-fr":
    case "fr":
      self::$ms_localeName = "fr";
    self::$ms_locale     = $g_localeFr;
    return true;

    case "en-us":
    case "en":
      self::$ms_localeName = "en";
    self::$ms_locale     = $g_localeEn;
    return true;

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