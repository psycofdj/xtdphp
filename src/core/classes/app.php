<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

require_once(__WAPPCORE_DIR__  . "/core/classes/log.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/menu.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/locale.php");
require_once(__WAPPCORE_DIR__  . "/core/libs/ezcomponents/load.php");

class App
{
  private static $ms_instance = null;
  private $m_menu             = null;
  private $m_handler          = null;
  private $m_modules          = Array();

  static public function get()
  {
    if (null == self::$ms_instance) {
      self::$ms_instance = new App();
      self::$ms_instance->loadModules(__WAPPCORE_DIR__);
      self::$ms_instance->loadModules(__APP_DIR__);
      self::$ms_instance->init();
    }
    return self::$ms_instance;
  }

  private function __construct()
  {
    $this->m_menu = new Menu();
  }

  private function init()
  {
    usort($this->m_modules, function(Module $p_e1, Module $p_e2) {
        return $p_e1->getPriority() > $p_e2->getPriority();
      });

    foreach ($this->m_modules as $c_module)
    {
      $c_module->initialize($this);

      // 2.
      foreach (Array("fr", "en") as $c_lang)
      {
        $l_path = sprintf("%s/%s/locales/%s.php", $c_module->getBaseDir(), $c_module->getName(), $c_lang);
        if (false == is_file($l_path))
          continue;

        require_once($l_path);
        $l_funcName = sprintf("%s_%s", $c_module->getName(), $c_lang);
        locale::addData($c_lang, $l_funcName());
      }
    }
  }

  public function initialize($p_handler)
  {
    $this->m_menu->initialize($p_handler);
    $this->m_handler = $p_handler;
  }

  /**
   * Find modules in source directory
   */
  private function loadModules($p_baseDir)
  {
    $l_result = array();
    if (false == $l_handle = opendir($p_baseDir))
    {
      log::error("core.app", "unable to open directory %s", $p_baseDir);
      return false;
    }

    while (false !== ($l_name = readdir($l_handle)))
    {
      $l_path = sprintf("%s/%s/load.php", $p_baseDir, $l_name);
      if (false == is_file($l_path))
        continue;
      if ($l_name == ".")
        $l_name = "main";
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
    require_once($p_modulePath);
    // 1.
    $l_className = sprintf("%sModule", $p_moduleName);
    array_push($this->m_modules, new $l_className($p_baseDir, $p_moduleName));
  }

  public function getHandler()
  {
    return $this->m_handler;
  }

  public function setHandler(Handler $p_handler)
  {
    $this->m_handler = $p_handler;
  }

  public function connect($p_className, $p_signal, $p_slot)
  {
    ezcSignalStaticConnections::getInstance()->connect($p_className, $p_signal, $p_slot);
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

  public function getMenu()
  {
    return $this->m_menu;
  }
}

?>