<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

class RoleModel
{
  static function getByID($p_rid)
  {
    return R::safeload("authrole", $p_rid);
  }

  static function getAll()
  {
    return R::find("authrole");
  }

  static function __update($p_role, $p_name, $p_actions)
  {
    $p_role->name                 = $p_name;
    $p_role->sharedAuthactionList = $p_actions;


    if (0 == count($p_actions))
      $p_role->datatype = null;
    else
      $p_role->datatype = $p_actions[0]->datatype;

    try {
      R::store($p_role);
    }
    catch (RedBeanPHP\RedException\SQL $l_error) {
      return array(false, $l_error->getSQLState());
    }
    return array($p_role, 0);
  }

  static function create($p_name, $p_actions)
  {
    $l_role = R::dispense("authrole");
    return self::__update($l_role, $p_name, $p_actions);
  }

  static function update($p_rid, $p_name, $p_actions)
  {
    if (false == ($l_role = self::getByID($p_rid)))
      return array(false, 0);
    return self::__update($l_role, $p_name, $p_actions);
  }

  static function delete($p_role)
  {
    R::trash($p_role);
    return true;
  }
}

?>