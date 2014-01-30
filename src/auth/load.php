<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/module.php");
require_once(__WAPPCORE_DIR__  . "/core/app.php");
require_once(__WAPPCORE_DIR__  . "/core/menu.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/user.php");

class authModule extends Module
{
  public function __construct()
  {
    parent::__construct("auth");

    App::get()->getMenu()
      ->addTab(new MenuTab("auth.menu.title"), 80)
      ->addSubTab("auth.menu.roles", "/wappcore/auth/roles.php")
      ->addSubTab("auth.menu.users", "/wappcore/auth/users.php");

    App::get()->getMenu()
      ->addWidget("file:[auth]menu_widget.tpl", array($this, "createWidget"));
  }

  public function createWidget(HtmlHandler $p_handler)
  {
    $p_handler->setData("auth_user", null);

    if (false != ($l_user = $p_handler->getSession("auth_user")))
      $p_handler->setData("auth_user",   $l_user);
  }
}

?>