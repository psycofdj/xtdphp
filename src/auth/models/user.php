<?php

class UserModel
{
  static function getByID($p_id)
  {
    return R::load("authuser", $p_id);
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
    R::store($l_user);
    return $l_user;
  }

  static function update($p_uid, $p_mail, $p_name, $p_password, &$p_isUpdated)
  {
    $l_user = R::load("authuser", $p_uid);

    $p_isUpdated = ($l_user->mail != $p_mail) || ($l_user->name != $p_name);

    $l_user->mail = $p_mail;
    $l_user->name = $p_name;
    if (0 != strlen($p_password))
    {
      $p_isUpdated = $p_isUpdated || ($l_user->password != md5($p_password));
      $l_user->password = md5($p_password);
    };

    R::store($l_user);
    return $l_user;
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
}

?>