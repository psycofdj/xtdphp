<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/module.php");
require_once(__WAPPCORE_DIR__  . "/core/app.php");
require_once(__WAPPCORE_DIR__  . "/core/menu.php");

class examplesModule extends Module
{
  public function __construct($p_baseDir, $p_name)
  {
    parent::__construct($p_baseDir, $p_name);

    /* App::get()->getMenu() */
    /*   ->addTab(new MenuTab("examples.menu.title"), 100) */
    /*   ->addSubTab("examples.menu.array", "/wappcore/examples/?action=array"); */
  }
}

?>