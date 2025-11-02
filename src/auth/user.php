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
require_once(__WAPPCORE_DIR__  . "/core/classes/mail.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/error.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/tools.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/user.php");
require_once(__WAPPCORE_DIR__  . "/auth/models/role.php");

class Page extends HTTPHandler
{
  public function __construct()
  {
    parent::__construct();

    $l_auth = App::get()->getModule("auth");
    $l_auth
      ->registerPerm("default", "auth/user/view")
      ->registerPerm("list",    "auth/user/view")
      ->registerPerm("edit",    "auth/user/modify")
      ->registerPerm("save",    "auth/user/modify")
      ->registerPerm("add",     "auth/user/modify")
      ->registerPerm("delete",  "auth/user/terminate");
  }


  public function h_default()
  {
    return $this->h_list();
  }


  public function h_list()
  {
    $this->setContent("[auth]user_list.tpl");
    $this->setData("users", UserModel::getAll());
    return true;
  }


  public function h_save($pi_uid = 0, $pm_email, $p_name, $p_password, $pau_perm = array(), $pau_resource = array())
  {
    if ((0 == $pi_uid) && (0 == strlen($p_password)))
    {
      log::crit("auth.user.save", "new users must have non-empty passwords");
      return false;
    }

    if (0 != $pi_uid)
    {
      $l_isUpdated = false;
      list($l_user, $l_err) = UserModel::update($pi_uid, $pm_email, $p_name, $p_password, $l_isUpdated);
    }
    else
    {
      $l_isUpdated = true;
      list($l_user, $l_err) = UserModel::create($pm_email, $p_name, $p_password);
    }

    if (false == $l_user)
    {
      if ($l_err == 23000)
      {
        $l_msg= t("auth.user.add.error.alreadyexists", $pm_email);
        throw new WappError($l_msg, 200, "/wappcore/auth/user.php");
      }
      log::crit("auth.user.save", "error while accessing/creating user");
      return false;
    }

    // 1.
    if ($l_isUpdated)
    {
      $l_mail = new MailTemplate("userinfo", $l_user->mail, true, $this);
      $l_mail
        ->setData("user",     $l_user)
        ->setData("password", $p_password)
        ->send();
    }


    // 2.
    $l_perms = array();
    foreach ($pau_perm as $c_permIdx)
    {
      $l_roleName = sprintf("perm_%d_role", $c_permIdx);
      $l_dataName = sprintf("perm_%d_data", $c_permIdx);

      if ((false === ($l_roleID = $this->getParam($l_roleName))) ||
          (false === ($l_dataID = $this->getParam($l_dataName))))
      {
        log::crit("auth.user.save", "could not find role '%s' and data '%s' id form permission index '%d'", $l_roleName, $l_dataName, $c_permIdx);
        return false;
      }

      if ($l_dataID == "")
        $l_dataID = null;

      if (false == RoleModel::getByID($l_roleID))
      {
        log::crit("auth.user.save", "unable to find roleID '%d'", $l_roleID);
        return false;
      }

      array_push($l_perms, array("role" => $l_roleID, "data" => $l_dataID));
    }
    UserModel::setPermissions($l_user, $l_perms);

    // 3.
    $l_resources = array();
    foreach ($pau_resource as $c_idx)
    {
      $l_nameName = sprintf("resource_%d_name", $c_idx);
      $l_nameID   = sprintf("resource_%d_id",   $c_idx);

      if ((false === ($l_name  = $this->getParam($l_nameName))) ||
          (false === ($l_id    = $this->getParam($l_nameID))))
      {
        log::crit("auth.user.save", "could not find name '%s' and/or value '%s' for resoures id '%d'", $l_nameName, $l_nameID, $c_idx);
        return false;
      }
      array_push($l_resources, array("name" => $l_name, "value" => $l_id));
    }
    UserModel::setResources($l_user, $l_resources);
    ConfigModel::set("flush", sprintf("%s", time()));

    return $this->redirect("/wappcore/auth/user.php");
  }

  public function h_edit($pu_uid)
  {
    $this->setContent("[auth]user_add.tpl");
    $this->setData("user",      UserModel::getByID($pu_uid));
    $this->setData("roles",     RoleModel::getAll());
    $this->setData("resources", App::get()->getModule("auth")->getResources());
    return true;
  }

  public function h_delete($pu_uid)
  {
    if (false == UserModel::delete($pu_uid))
    {
      log::crit("auth.user.delete", "unable to delete user of id '%d'", $pu_uid);
      return false;
    }
    ConfigModel::set("flush", sprintf("%s", time()));
    return $this->redirect("/wappcore/auth/user.php");
  }

  public function h_add()
  {
    $this->setContent("[auth]user_add.tpl");
    $this->setData("roles", RoleModel::getAll());
    $this->setData("resources", App::get()->getModule("auth")->getResources());
    return true;
  }

  public function h_recover($p_email = null)
  {
    $this->setContent("[auth]user_recover.tpl");
    $this->setData("mail",   "");
    $this->setData("status", "none");

    if ($p_email == null)
      return true;

    $this->setData("mail", $p_email);
    if (false == ($l_user = UserModel::getByMail($p_email)))
    {
      $this->setData("status", "notfound");
      return true;
    }

    $l_newPassword = tools::genPassword(8, 8);
    list($l_user, $l_error) = UserModel::update($l_user->id, $l_user->mail, $l_user->name, $l_newPassword, $l_dummy);
    if (false === $l_user)
    {
      log::error("auth.user.recover", "unable to update password for user '%s'", $p_email);
      return false;
    }

    $l_mail = new MailTemplate("userrecover", $p_email, true, $this);
    $l_mail
      ->setData("user",     $l_user)
      ->setData("password", $l_newPassword)
      ->send();
    $this
      ->setData("user",  $l_user)
      ->setData("status", "ok");
    return true;
  }

}

$l_page = new Page();
$l_page->process();

?>