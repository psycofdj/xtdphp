<?php

require_once(dirname(__FILE__) . "/../../local.php");
require_once(__WAPPCORE_DIR__  . "/core/libs/RedBeanPHP/loader.php");
require_once(__WAPPCORE_DIR__  . "/core/classes/log.php");

/**
 * Simple redbean request logger
 */
class SqlLogger implements RedBeanPHP\Logger
{
  public function log()
  {
    foreach (func_get_args() as $c_arg)
    {
      if (is_array($c_arg))
      {
        if (0 == count($c_arg))
          continue;
        $c_arg = print_r($c_arg, true);
      }
      $c_arg = str_replace("\n", " ",  $c_arg);
      $c_arg = preg_replace('/( +)/s', ' ', $c_arg);
      if (false != strstr($c_arg, "SQLSTATE")) {
        log::crit("core.sql", "sql error : %s", $c_arg);
      }
      else
        log::debug("core.sql", "executing sql query : %s", $c_arg);
    }
  }
}


class sql
{

  static function initialize()
  {
    global $g_conf;

    $l_conf = sprintf("mysql:host=%s;dbname=%s;", $g_conf["mysql"]["host"], $g_conf["mysql"]["database"]);
    R::setup($l_conf, $g_conf["mysql"]["username"], $g_conf["mysql"]["password"]);
    R::getDatabaseAdapter()->getDatabase()->setDebugMode(true, new SqlLogger());
    R::freeze(true);
    R::ext("safeload", function($p_model, $p_id) {
        return sql::safeLoad($p_model, $p_id);
      });
  }

  static function safeLoad($p_model, $p_id)
  {
    $l_item = R::load($p_model, $p_id);
    if ($l_item->id == 0)
    {
      log::error(sprintf("%s.model", $p_model), "unable to get %s id '%d'", $p_model, $p_id);
      return false;
    }
    return $l_item;
  }
}


class SqlImporter
{
  public function load($p_file)
  {
    if (false == ($l_data = file_get_contents($p_file)))
    {
      log::crit("core.sql", "enable to open file '%s'", $p_file);
      return false;
    }

    R::begin();
    R::exec($l_data);
    R::commit();
    return true;
  }
}

?>