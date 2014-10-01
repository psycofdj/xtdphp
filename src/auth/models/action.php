<?php

class ActionModel
{
  static function getByID($p_rid)
  {
    return R::safeload("authaction", $p_rid);
  }

  static function getAll()
  {
    return R::findAll("authaction");
  }

  static function delete($p_role)
  {
    R::trash($p_role);
    return true;
  }
}

?>