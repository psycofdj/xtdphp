<?php

class UserModel
{
  static function getByID(int $p_id)
  {
    return R::findOne("user", "id = :id", array("id" => $p_id));
  }

  static function getByMailPass($p_mail, $p_password)
  {
    return R::findOne("user", "mail = :mail and password = :password",
                      array("mail"     => $p_mail,
                            "password" => md5($p_password)));
  }
}

?>