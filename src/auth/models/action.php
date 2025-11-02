<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

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