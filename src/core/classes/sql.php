<?php
/**
 ** Copyright (C) 2015 All Rights Reserved
 **
 ** Written by: Xavier MARCELET <xavier@marcelet.com>, 20008-2016, France
 **
 ** Unauthorized copying of this file, via any medium is strictly prohibited
 ** Proprietary and confidential
 */

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
      if (false != strstr($c_arg, "SQLSTATE"))
        log::crit("core.sql", "sql error : %s", $c_arg);
      else
        log::debug("core.sql", "executing sql format query : %s", $c_arg);
    }
  }
}


class sql
{
  const success   = 0;
  const duplicate = 1;
  const unknown   = 2;

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
    R::ext("safestore", function($p_bean) {
        return sql::safeStore($p_bean);
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

  static function safeStore($p_bean)
  {
    try {
      R::store($p_bean);
    }
    catch (RedBeanPHP\RedException\SQL $l_error) {
      switch ($l_error->getSQLState())
      {
      case 23000:
        $l_errorType = self::duplicate;
        break;
      default:
        $l_errorType = self::unknown;
        break;
      }
      return array($l_errorType, $p_bean);
    }

    return array(self::success, $p_bean);
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