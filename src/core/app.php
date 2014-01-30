<?php

require_once(__WAPPCORE_DIR__  . "/core/log.php");
require_once(__WAPPCORE_DIR__  . "/core/menu.php");

class App
{
  private static $ms_instance = null;
  private $m_menu             = null;
  private $m_modules          = Array();
  private $m_locales          = Array("fr" => Array(),
                                      "en" => Array());

  private function __construct()
  {
    $this->m_menu = new Menu();
  }

  public function get()
  {
    if (null == self::$ms_instance) {
      self::$ms_instance = new App();
      self::$ms_instance->loadModules();
    }
    return self::$ms_instance;
  }

  /**
   * Find modules in source directory
   */
  private function loadModules()
  {
    if (false == $l_handle = opendir(__WAPPCORE_DIR__))
    {
      log::error("unable to open directory %s", __WAPPCORE_DIR__);
      return false;
    }

    while (false !== ($l_name = readdir($l_handle)))
    {
      $l_path = sprintf("%s/%s/load.php", __WAPPCORE_DIR__, $l_name);
      if (false == is_file($l_path))
        continue;
      $this->addModule($l_path, $l_name);
    }

    closedir($l_handle);
  }

  /**
   * Load given module
   *
   * 1. source load file and create module object
   * 2. search for locale data
   */
  private function addModule($p_modulePath, $p_moduleName)
  {
    require_once($p_modulePath);

    // 1.
    $l_className = sprintf("%sModule", $p_moduleName);
    array_push($this->m_modules, new $l_className());

    // 2.
    foreach (Array("fr", "en") as $c_lang)
    {
      $l_path = sprintf("%s/%s/locales/%s.php", __WAPPCORE_DIR__, $p_moduleName, $c_lang);
      if (false == is_file($l_path))
        continue;

      require_once($l_path);
      $l_funcName = sprintf("%s_%s", $p_moduleName, $c_lang);
      $this->m_locales[$c_lang] = array_merge($this->m_locales[$c_lang], $l_funcName());
    }
  }


  public function getModules()
  {
    return $this->m_modules;
  }

  public function getLocale($p_name)
  {
    if (array_key_exists($p_name, $this->m_locales))
      return $this->m_locales[$p_name];

    log::warn("requested unknown locale '%s'", $p_name);
    return Array();
  }

  public function getMenu()
  {
    return $this->m_menu;
  }
}

?>
