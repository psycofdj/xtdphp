<?php

require_once(__WAPPCORE_DIR__  . "/core/log.php");

class Module
{
  private       $m_name;
  private       $m_menu;
  private       $m_widgets;
  public static $ms_langs;
  public static $ms_modules;

  static function init()
  {
    self::$ms_modules = Array();
    self::$ms_langs   = Array("fr" => Array(),
                              "en" => Array());

    if (false == $l_handle = opendir(__WAPPCORE_DIR__))
    {
      log::error("unable to open directory %s", __WAPPCORE_DIR__);
      return false;
    }

    while (false !== ($l_item = readdir($l_handle)))
    {
      $l_path = sprintf("%s/%s/load.php", __WAPPCORE_DIR__, $l_item);
      if (false == is_file($l_path))
        continue;
      require_once($l_path);
      $l_className = sprintf("%sModule", $l_item);
      array_push(self::$ms_modules, new $l_className());

      foreach (Array("fr", "en") as $c_lang) {
        $l_path = sprintf("%s/%s/locales/%s.php", __WAPPCORE_DIR__, $l_item, $c_lang);
        if (false == is_file($l_path))
          continue;
        log::debug($l_path);
        require_once($l_path);

        $l_funcName = sprintf("%s_%s", $l_item, $c_lang);
        self::$ms_langs[$c_lang] = array_merge(self::$ms_langs[$c_lang], $l_funcName());
      }
    }

    closedir($l_handle);
  }

  static function getModules()
  {
    return self::$ms_modules;
  }

  static function getLang($p_langName)
  {
    return self::$ms_langs[$p_langName];
  }

  public function getName()
  {
    return $this->m_name;
  }

  protected function __construct($p_name)
  {
    log::debug("initializing module '%s'", $p_name);
    $this->m_name    = $p_name;
    $this->m_menu    = Array();
    $this->m_widgets = Array();
  }

  protected function addMenuComposed($p_title, $p_sections)
  {
    array_push($this->m_menu,
               Array("title"    => $p_title,
                     "sections" => $p_sections));
  }

  protected function addMenu($p_link, $p_title, $p_role)
  {
    array_push($this->m_menu,
               Array("title" => $p_title,
                     "link"  => $p_link,
                     "role"  => $p_role));
  }

  public function getMenu()
  {
    return $this->m_menu;
  }

  public function getWidgets()
  {
    return $this->m_widgets;
  }

  protected function addMenuWidget($p_template, $p_callback = null)
  {
    array_push($this->m_widgets,
               Array("tpl"      => $p_template,
                     "callback" => $p_callback));
  }
}