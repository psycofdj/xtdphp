<?php

class UserModel
{
  static function getByID(int $p_id)
  {
    return R::findOne("user", "id = :id", array("id" => $p_id));
  }
}

?>