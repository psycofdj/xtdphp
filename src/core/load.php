<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/module.php");

class coreModule extends Module
{
  public function __construct()
  {
    parent::__construct("core");

    $this->addMenu("/index.php", "core.menu.home", "public");
  }
}

?>