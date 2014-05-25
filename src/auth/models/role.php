<?php

class RoleModel
{
  static function getByID($p_rid)
  {
    return R::load("authrole", $p_rid);
  }

  static function getAll()
  {
    return R::findAll("authrole");
  }


  static function delete($p_role)
  {
    R::trash($p_role);
    return true;
  }
}

?>