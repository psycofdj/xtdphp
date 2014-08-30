<?php

class ConfigModel
{
  static function getByID($p_id)
  {
    $l_conf = R::load("authconfig", $p_id);
    if ($l_conf->id == 0)
      return false;
    return $l_conf;
  }

  static function getByName($p_name)
  {
    return R::findOne("authconfig", "name = :name", array("name" => $p_name));
  }

  static function save($p_conf)
  {
    try {
      R::store($p_conf);
      return array($p_conf, 0);
    }
    catch (RedBeanPHP\RedException\SQL $l_error) {
      return array(false, $l_error->getSQLState());
    }
  }

  static function set_or_create($p_name, $p_value)
  {
    if (false == ($l_conf = self::getByName($p_name)))
      return self::create($p_name, $p_value);
    $l_conf->value = $p_value;
    return self::save($l_conf);
  }

  static function set($p_name, $p_value)
  {
    if (false == ($l_conf = self::getByName($p_name)))
      return array(false, 0);
    $l_conf->value = $p_value;
    return self::save($l_conf);
  }

  static function create($p_name, $p_value)
  {
    $l_conf        = R::dispense("authconfig");
    $l_conf->name  = $p_name;
    $l_conf->value = $p_value;
    return self::save($l_conf);
  }

  static function get($p_name)
  {
    if (false == ($l_conf = self::getByName($p_name)))
      return false;
    return $l_conf->value;
  }
}

?>