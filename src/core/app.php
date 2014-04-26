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

  static public function get()
  {
    if (null == self::$ms_instance) {
      self::$ms_instance = new App();
      self::$ms_instance->loadModules(__WAPPCORE_DIR__);
      self::$ms_instance->loadModules(__APP_DIR__);
    }
    return self::$ms_instance;
  }

  /**
   * Find modules in source directory
   */
  private function loadModules($p_baseDir)
  {
    if (false == $l_handle = opendir($p_baseDir))
    {
      log::error("unable to open directory %s", $p_baseDir);
      return false;
    }

    while (false !== ($l_name = readdir($l_handle)))
    {
      $l_path = sprintf("%s/%s/load.php", $p_baseDir, $l_name);
      if (false == is_file($l_path))
        continue;
      $this->addModule($p_baseDir, $l_path, $l_name);
    }

    closedir($l_handle);
  }

  /**
   * Load given module
   *
   * 1. source load file and create module object
   * 2. search for locale data
   */
  private function addModule($p_baseDir, $p_modulePath, $p_moduleName)
  {
    ob_start();
    require_once($p_modulePath);

    // 1.
    $l_className = sprintf("%sModule", $p_moduleName);
    array_push($this->m_modules, new $l_className($p_baseDir, $p_moduleName));

    // 2.
    foreach (Array("fr", "en") as $c_lang)
    {
      $l_path = sprintf("%s/%s/locales/%s.php", $p_baseDir, $p_moduleName, $c_lang);
      if (false == is_file($l_path))
        continue;
      require_once($l_path);
      $l_funcName = sprintf("%s_%s", $p_moduleName, $c_lang);
      $this->m_locales[$c_lang] = array_merge($this->m_locales[$c_lang], $l_funcName());
    }
    ob_end_clean();
  }

  public function getModules()
  {
    return $this->m_modules;
  }

  public function getModule($p_moduleName)
  {
    $l_modules = array_filter($this->m_modules, function($p_el) use (&$p_moduleName) {
        return ($p_el->getName() == $p_moduleName);
      });

    if (1 != count($l_modules))
      return false;
    return array_shift($l_modules);
  }

  public function getLocale($p_name)
  {
    if (array_key_exists($p_name, $this->m_locales))
      return $this->m_locales[$p_name];
    log::warn("requested unknown locale '%s'", $p_name);
    return array();
  }

  public function getMenu()
  {
    return $this->m_menu;
  }

  public function initialize($p_handler)
  {
    $this->m_menu->initialize($p_handler);
  }
}

?>