<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/module.php");

class coreModule extends Module
{
  public function __construct()
  {
    parent::__construct("core");

    $this->addMenu("/index.php", "core.menu.home", "public");

    $this->addMenuComposed("core.menu.lang",
                           Array(Array("link"  => "/wappcore/core/lang.php?lang=fr",
                                       "title" => "core.menu.lang.fr",
                                       "role"  => "public"),
                                 Array("link"  => "/wappcore/core/lang.php?lang=en",
                                       "title" => "core.menu.lang.en",
                                       "role"  => "public")));

  }
}

?>