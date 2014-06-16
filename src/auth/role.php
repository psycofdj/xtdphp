<?php

require_once(dirname(__FILE__) . "/../local.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/handler.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/role.php");

class Page extends Handler
{
  public function __construct()
  {
    parent::__construct();

    $l_auth = App::get()->getModule("auth");
    $l_auth
      ->registerPerm("default", "auth/user/view")
      ->registerPerm("list",    "auth/user/view")
      ->registerPerm("edit",    "auth/user/modify")
      ->registerPerm("delete",  "auth/user/terminate");
  }

  public function h_default()
  {
    return $this->h_list();
  }

  public function h_list()
  {
    $this->setContent("file:[auth]role_list.tpl");
    $this->setData("roles", RoleModel::getAll());
    return true;
  }

  /* public function h_save($pi_uid = 0, $pm_email, $p_name, $p_password, $pau_perm = array()) */
  /* { */
  /*   if ((0 == $pu_uid) && (0 == strlen($p_password))) */
  /*   { */
  /*     log::crit("new users must have non-empty passwords"); */
  /*     return false; */
  /*   } */

  /*   if (0 == $pu_uid) */
  /*     $l_user = UserModel::create($pm_email, $p_name, $p_password); */
  /*   else */
  /*     $l_user = UserModel::update($pu_uid, $pm_email, $p_name, $p_password); */

  /*   if (false == $l_user) */
  /*   { */
  /*     log::crit("error while accessing/creating user"); */
  /*     return false; */
  /*   } */

  /*   $l_perms = array(); */
  /*   foreach ($pau_perm as $c_permIdx) */
  /*   { */
  /*     $l_roleName = sprintf("perm_%d_role", $c_permIdx); */
  /*     $l_dataName = sprintf("perm_%d_data", $c_permIdx); */

  /*     if ((false === ($l_roleID = $this->getParam($l_roleName))) || */
  /*         (false === ($l_dataID = $this->getParam($l_dataName)))) */
  /*     { */
  /*       log::crit("could not find role '%s' and data '%s' id form permission index '%d'", $l_roleName, $l_dataName, $c_permIdx); */
  /*       return false; */
  /*     } */

  /*     if ($l_dataID == "") */
  /*       $l_dataID = null; */

  /*     if (false == RoleModel::getByID($l_roleID)) */
  /*     { */
  /*       log::crit("unable to find roleID '%d'", $l_roleID); */
  /*       return false; */
  /*     } */

  /*     array_push($l_perms, array("role" => $l_roleID, "data" => $l_dataID)); */
  /*   } */

  /*   UserModel::setPermissions($l_user, $l_perms); */
  /*   return $this->redirect("/wappcore/auth/user.php"); */
  /* } */

  /* public function h_edit($pu_uid) */
  /* { */
  /*   $this->setContent("file:[auth]user_add.tpl"); */
  /*   $this->setData("user",      UserModel::getByID($pu_uid)); */
  /*   $this->setData("roles",     RoleModel::getAll()); */
  /*   $this->setData("resources", App::get()->getModule("auth")->getResources()); */
  /*   return true; */
  /* } */

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

    return $this->redirect("/wappcore/auth/role.php");
  }

  /* public function h_add() */
  /* { */
  /*   $this->setContent("file:[auth]user_add.tpl"); */
  /*   $this->setData("roles", RoleModel::getAll()); */
  /*   return true; */
  /* } */
}

$l_page = new Page();
$l_page->process();

?>