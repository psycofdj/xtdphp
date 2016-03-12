<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/module.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/app.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/menu.php");

class coreModule extends Module
{
  public function __construct($p_baseDir, $p_name)
  {
    parent::__construct($p_baseDir, $p_name, 0);
  }

  public function initialize($p_app)
  {
    $p_app->getMenu()
      ->addTab(new MenuTab("core.menu.lang"), 100)
      ->addSubTab("core.menu.lang.en", "/wappcore/core/lang.php?lang=en")
      ->addSubTab("core.menu.lang.fr", "/wappcore/core/lang.php?lang=fr");
  }

  function setup()
  {
    R::exec(<<<EOF
      CREATE TABLE `session` (
        `id`        int(11) unsigned NOT NULL AUTO_INCREMENT,
        `sid`       varchar(256)     NOT NULL,
        `timestamp` int(11) unsigned NOT NULL,
        `data`      longtext             NOT NULL,
        PRIMARY KEY (`id`),
        KEY         `sid`       (`sid`(255)),
        KEY         `timestamp` (`timestamp`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOF
           );
  }

}

?>