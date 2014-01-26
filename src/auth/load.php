<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/module.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/user.php");

class authModule extends Module
{
  public function __construct()
  {
    parent::__construct("auth");

    $this->addMenuComposed("auth.menu.title",
                           Array(Array("link"  => "/wappcore/auth/roles.php",
                                       "title" => "auth.menu.roles",
                                       "role"  => "admin"),
                                 Array("link"  => "/wappcore/auth/users.php",
                                       "title" => "auth.menu.users",
                                       "role"  => "admin")));

    $this->addMenuWidget("[auth]menu_widget.tpl", array($this, "createWidget"));
  }

  public function createWidget(HtmlHandler $p_handler)
  {
    $p_handler->setData("auth_logged", false);

    if ((false != ($l_userID = $p_handler->getSession("auth_logged"))) &&
        (null  != ($l_user   = UserModel::getByID($l_userID))))
    {
      $p_handler->setData("auth_logged", true);
      $p_handler->setData("auth_user",   $l_user);
    }
  }
}

?>