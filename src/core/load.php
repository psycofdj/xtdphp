<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/module.php");
require_once(__WAPPCORE_DIR__  . "/core/app.php");
require_once(__WAPPCORE_DIR__  . "/core/menu.php");

class coreModule extends Module
{
  public function __construct($p_baseDir, $p_name)
  {
    parent::__construct($p_baseDir, $p_name);

    App::get()->getMenu()
      ->addTab(new MenuTab("core.menu.home", "/"), 10);

    App::get()->getMenu()
      ->addTab(new MenuTab("core.menu.lang"), 100)
      ->addSubTab("core.menu.lang.fr", "/wappcore/core/lang.php?lang=fr")
      ->addSubTab("core.menu.lang.en", "/wappcore/core/lang.php?lang=en");
  }
}

?>