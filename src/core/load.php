<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/module.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/app.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/menu.php");

class coreModule extends Module
{
  public function __construct($p_baseDir, $p_name)
  {
    parent::__construct($p_baseDir, $p_name, 0);
    App::get()->getMenu()
      ->addTab(new MenuTab("core.menu.lang"), 100)
      ->addSubTab("core.menu.lang.en", "/wappcore/core/lang.php?lang=en")
      ->addSubTab("core.menu.lang.fr", "/wappcore/core/lang.php?lang=fr");

    App::get()->getMenu()
      ->addTab(new MenuTab("core.menu.setup"), 120)
      ->addSubTab("core.menu.setup.install", "/wappcore/core/setup.php");
  }
}

?>
