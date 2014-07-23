<?php

class RoleModel
{
  static function getByID($p_rid)
  {
    $l_role = R::load("authrole", $p_rid);
    if ($l_role->id == 0)
      return false;
    return $l_role;
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