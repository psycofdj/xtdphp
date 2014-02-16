<?php

class UserModel
{
  static function getByID(int $p_id)
  {
    return R::findOne("users", "id = :id", array("id" => $p_id));
  }

  static function getByMailPass($p_mail, $p_password)
  {
    return R::findOne("users", "mail = :mail and password = :password",
                      array("mail"     => $p_mail,
                            "password" => md5($p_password)));
  }

  static function getAll()
  {
    return R::find("users");
  }

  /* static function getUserRoles($p_user) */
  /* { */
  /*   return R::find("roles" */
  /* } */

}

?>