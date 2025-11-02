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
require_once(__WAPPCORE_DIR__  . "/core/classes/handler.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/role.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/action.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/config.php");

class Page extends HTTPHandler
{
  public function __construct()
  {
    parent::__construct();

    $l_auth = App::get()->getModule("auth");
    $l_auth
      ->registerPerm("default", "auth/role/view")
      ->registerPerm("list",    "auth/role/view")
      ->registerPerm("delete",  "auth/role/terminate")
      ->registerPerm("add",     "auth/role/modify")
      ->registerPerm("edit",    "auth/role/modify")
      ->registerPerm("save",    "auth/role/modify");
  }

  public function h_default()
  {
    return $this->h_list();
  }

  public function h_list()
  {
    $this->setContent("[auth]role_list.tpl");
    $this->setData("roles", RoleModel::getAll());
    return true;
  }

  public function h_delete($pu_rid)
  {
    if (false == ($l_role = RoleModel::getByID($pu_rid)))
    {
      log::crit("auth.role.delete", "unable to get role of id '%d'", $pu_rid);
      return false;
    }

    if (false == RoleModel::delete($l_role))
    {
      log::crit("auth.role.delete", "unable to delete role of id '%d'", $pu_rid);
      return false;
    }

    ConfigModel::set("flush", sprintf("%s", time()));
    return $this->redirect("/wappcore/auth/role.php");
  }

  public function h_add()
  {
    $this->setContent("[auth]role_add.tpl");

    $this->setData("actions",   ActionModel::getAll());
    $this->setData("resources", App::get()->getModule("auth")->getResources());
    return true;
  }

  public function h_edit($pu_rid)
  {
    $this->setContent("[auth]role_add.tpl");
    $this->setData("role",      RoleModel::getByID($pu_rid));
    $this->setData("actions",   ActionModel::getAll());
    return true;
  }

  public function h_save($pu_rid, $p_name, $pau_aid = array())
  {
    $l_actions = array();
    foreach ($pau_aid as $c_aid)
    {
      if (false == ($l_action = ActionModel::getByID($c_aid)))
      {
        log::crit("auth.role", "unknown action id '%d'", $c_aid);
        return false;
      }
      array_push($l_actions, $l_action);
    }

    if (0 == $pu_rid)
      list($l_role, $l_error) = RoleModel::create($p_name, $l_actions);
    else
      list($l_role, $l_error) = RoleModel::update($pu_rid, $p_name, $l_actions);

    if (false == $l_role)
    {
      if (23000 == $l_error)
      {
        $l_msg = t("auth.role.add.error.alreadyexists", $p_name);
        throw new WappError($l_msg, 200, "/wappcore/auth/role.php");
      }
      log::crit("auth.role.save", "unable to create role");
      return false;
    }
    ConfigModel::set("flush", sprintf("%s", time()));
    return $this->redirect("/wappcore/auth/role.php");
  }
}

$l_page = new Page();
$l_page->process();

?>