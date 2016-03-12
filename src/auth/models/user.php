<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

class UserModel
{
  static function getByID($p_id)
  {
    return R::safeload("authuser", $p_id);
  }

  static function delete($p_id)
  {
    if (false == ($l_user = UserModel::getByID($p_id)))
      return false;

    UserModel::setPermissions($l_user, array());
    R::trash($l_user);
    return true;
  }

  static function getByMailPass($p_mail, $p_password)
  {
    return R::findOne("authuser", "mail = :mail and password = :password",
                      array("mail" => $p_mail, "password" => md5($p_password)));
  }

  static function getByMail($p_mail)
  {
    return R::findOne("authuser", "mail = :mail", array("mail" => $p_mail));
  }

  static function getAll()
  {
    return R::find("authuser");
  }

  static function create($p_mail, $p_name, $p_password)
  {
    $l_user = R::dispense("authuser");
    $l_user->mail     = $p_mail;
    $l_user->name     = $p_name;
    $l_user->password = md5($p_password);

    try {
      R::store($l_user);
    }
    catch (RedBeanPHP\RedException\SQL $l_error) {
      return array(false, $l_error->getSQLState());
    }
    return array($l_user, 0);
  }


  static function update($p_uid, $p_mail, $p_name, $p_password, &$p_isUpdated)
  {
    $l_user = UserModel::getByID($p_uid);

    $p_isUpdated = ($l_user->mail != $p_mail) || ($l_user->name != $p_name);

    $l_user->mail = $p_mail;
    $l_user->name = $p_name;
    if (0 != strlen($p_password))
    {
      $p_isUpdated = $p_isUpdated || ($l_user->password != md5($p_password));
      $l_user->password = md5($p_password);
    };

    try {
      R::store($l_user);
    }
    catch (RedBeanPHP\RedException\SQL $l_error) {
      return array(false, $l_error->getSQLState());
    }
    return array($l_user, 0);
  }

  static function setPermissions(&$p_user, $p_perms)
  {
    $p_user->xownAuthuserAuthpermList = array();
    foreach ($p_perms as $c_perm)
    {
      $l_role = RoleModel::getByID($c_perm["role"]);
      $p_user->link("authuser_authperm", array("data" => $c_perm["data"]))->authrole = $l_role;
    }
    R::store($p_user);
  }


  static function setResources(&$p_user, $p_resources)
  {
    $p_user->xownAuthuserAuthresourceList = array();
    foreach ($p_resources as $c_res)
    {
      $p_user->link("authuser_authresource", array(
            "name"  => $c_res["name"],
            "value" => $c_res["value"]));
    }
    R::store($p_user);
  }
}

?>