<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/module.php");
require_once(__WAPPCORE_DIR__  . "/core/app.php");
require_once(__WAPPCORE_DIR__  . "/core/menu.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/user.php");


class Role
{
  public $m_tag;
  public $m_pos;

  public function __construct($p_tag, $p_pos)
  {
    $this->m_tag   = $p_tag;
    $this->m_pos   = $p_pos;
  }

  public function validFor($p_perm)
  {
    return (0 != ($p_perm & (1 << $this->m_pos)));
  }
}


class authModule extends Module
{
  private $m_perms = Array();

  public function __construct()
  {
    parent::__construct("auth");

    $this
      ->addPerm("auth.perm.user.view")
      ->addPerm("auth.perm.user.create")
      ->addPerm("auth.perm.user.update")
      ->addPerm("auth.perm.user.terminate")
      ->addPerm("auth.perm.role.view")
      ->addPerm("auth.perm.role.create")
      ->addPerm("auth.perm.role.update")
      ->addPerm("auth.perm.role.terminate");

    App::get()->getMenu()
      ->addTab(new MenuTab("auth.menu.title"), 80)
      ->addSubTab("auth.menu.roles", "/wappcore/auth/?action=rolelist", "auth.perm.role.*")
      ->addSubTab("auth.menu.users", "/wappcore/auth/?action=userlist", "auth.perm.user.*");

    App::get()->getMenu()
      ->addWidget("file:[auth]menu_widget.tpl", array($this, "createWidget"));
  }


  public function createWidget(HtmlHandler $p_handler)
  {
    $p_handler->setData("auth_user", null);
    $p_handler->setData("auth_perm", null);


    /* $p_handler->setData("auth",      App::get()->getModule("auth")); */

    if (false != ($l_user = $p_handler->getSession("auth_user")))
      $p_handler->setData("auth_user",   $l_user);
  }

  public function addPerm($p_tag)
  {
    array_push($this->m_perms, $p_tag);
    return $this;
  }

  public function getPerms()
  {
    return $this->m_perms;
  }

  /* public function getRolesOf($p_value) */
  /* { */
  /*   return array_filter($this->m_roles, function($p_el) use (&$p_value) { */
  /*       return $p_el->validFor($p_value); */
  /*     }); */
  /* } */

  /* public function hasRole($p_roleTag, $p_value) */
  /* { */
  /*   $l_roles = $this->getRolesOf($p_value); */
  /*   $l_roles = array_filter($l_roles, function($p_el) use (&$p_roleTag) { */
  /*       return ($p_el->m_tag == $p_roleTag); */
  /*     }); */
  /*   return (0 != count($l_roles)); */
  /* } */

}

?>